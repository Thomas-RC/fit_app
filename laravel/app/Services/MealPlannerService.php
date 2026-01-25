<?php

namespace App\Services;

use App\Models\MealPlan;
use App\Models\MealPlanRecipe;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MealPlannerService
{
    protected $spoonacularService;
    protected $vertexAIService;

    public function __construct(SpoonacularService $spoonacularService, VertexAIService $vertexAIService)
    {
        $this->spoonacularService = $spoonacularService;
        $this->vertexAIService = $vertexAIService;
    }

    /**
     * Generate a meal plan for a user based on their fridge items and preferences.
     * Uses HYBRID approach: findByIngredients when fridge has items, complexSearch otherwise.
     *
     * @param User $user
     * @param string $date
     * @return MealPlan|null
     */
    public function generateMealPlanForUser(User $user, string $date): ?MealPlan
    {
        try {
            // Get user preferences
            $preferences = $user->preferences;
            $dailyCalories = $preferences->daily_calories ?? 2000;

            // Get user's fridge items
            $fridgeItems = $user->fridgeItems()->pluck('product_name')->toArray();

            // Prepare preferences array
            $preferencesArray = [
                'diet_type' => $preferences->diet_type ?? 'omnivore',
                'allergies' => $preferences->allergies ?? [],
                'exclude_ingredients' => $preferences->exclude_ingredients ?? [],
                'daily_calories' => $dailyCalories,
            ];

            // Get recently used recipe IDs (last 30 days) to avoid repetition
            $recentRecipeIds = $user->mealPlans()
                ->where('date', '>=', now()->subDays(30))
                ->with('recipes')
                ->get()
                ->pluck('recipes')
                ->flatten()
                ->pluck('spoonacular_recipe_id')
                ->unique()
                ->toArray();

            Log::info('Starting meal plan generation', [
                'user_id' => $user->id,
                'date' => $date,
                'daily_calories' => $dailyCalories,
                'diet_type' => $preferencesArray['diet_type'],
                'has_fridge_items' => !empty($fridgeItems),
                'fridge_item_count' => count($fridgeItems),
            ]);

            // === DECISION TREE: Choose strategy based on fridge items ===

            if (!empty($fridgeItems)) {
                // === SCENARIO A: User HAS fridge items ===
                Log::info('Using complexSearch with fridge items and meal-specific parameters');

                $recipesByMealType = $this->fetchRecipesWithFridgeItems(
                    $fridgeItems,
                    $preferencesArray,
                    $dailyCalories
                );

                // Check if we got any recipes
                if (empty($recipesByMealType)) {
                    Log::error('No recipes found with fridge items');
                    return null;
                }

            } else {
                // === SCENARIO B: User has NO fridge items ===
                Log::info('Using DIRECT complexSearch strategy (no fridge items)');

                $recipesByMealType = $this->fetchRecipesByMealTypes(
                    $preferencesArray,
                    $dailyCalories
                );
            }

            // Check if we have recipes for each meal type
            foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                if (empty($recipesByMealType[$mealType])) {
                    Log::error("No recipes found for {$mealType}");
                    return null;
                }
            }

            Log::info('Recipes filtered by meal type', [
                'breakfast_count' => count($recipesByMealType['breakfast']),
                'lunch_count' => count($recipesByMealType['lunch']),
                'dinner_count' => count($recipesByMealType['dinner']),
            ]);

            // === Use VertexAI to select best recipe for each meal ===

            $selectedRecipes = [];

            foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                $candidates = $recipesByMealType[$mealType];

                // Use AI to select best recipe
                $selectedIds = $this->vertexAIService->selectBestRecipes(
                    $candidates,
                    $preferencesArray,
                    $recentRecipeIds,
                    $fridgeItems,
                    1  // Select only 1 recipe per meal type
                );

                if (empty($selectedIds)) {
                    // Fallback: random selection
                    shuffle($candidates);
                    $selectedRecipe = $candidates[0];
                } else {
                    // Find selected recipe
                    $filtered = array_filter($candidates, fn($r) => $r['id'] === $selectedIds[0]);
                    $selectedRecipe = !empty($filtered) ? reset($filtered) : $candidates[0];
                }

                $selectedRecipes[] = $selectedRecipe;
            }

            $totalCalories = array_sum(array_column($selectedRecipes, 'calories'));
            $calorieDeficit = $dailyCalories - $totalCalories;

            Log::info('Selected final recipes', [
                'recipe_ids' => array_column($selectedRecipes, 'id'),
                'recipe_titles' => array_column($selectedRecipes, 'title'),
                'meal_types' => array_column($selectedRecipes, 'meal_type'),
                'calories' => array_column($selectedRecipes, 'calories'),
                'total_calories' => $totalCalories,
                'target_calories' => $dailyCalories,
                'deficit' => $calorieDeficit
            ]);

            // If calorie deficit > 100 kcal, add a snack to reach target
            if ($calorieDeficit > 100) {
                Log::info('Calorie deficit detected, adding snack', [
                    'deficit' => $calorieDeficit,
                    'target_snack_calories' => $calorieDeficit
                ]);

                $snack = $this->findSnackToFillCalories($calorieDeficit, $preferencesArray, $fridgeItems);

                if ($snack) {
                    $snack['meal_type'] = 'snack';
                    $selectedRecipes[] = $snack;

                    Log::info('Snack added successfully', [
                        'snack_id' => $snack['id'],
                        'snack_title' => $snack['title'],
                        'snack_calories' => $snack['calories'],
                        'new_total' => array_sum(array_column($selectedRecipes, 'calories'))
                    ]);
                }
            }

            // Create meal plan from selected recipes (now 3 or 4 meals)
            return $this->createMealPlanFromRecipes($user, $date, $selectedRecipes, $dailyCalories);

        } catch (\Exception $e) {
            Log::error('Meal Planner Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Save meal plan from Spoonacular API response.
     */
    protected function saveMealPlan(User $user, string $date, array $mealPlanData): MealPlan
    {
        $totalCalories = $mealPlanData['nutrients']['calories'] ?? 0;

        $mealPlan = MealPlan::create([
            'user_id' => $user->id,
            'date' => $date,
            'total_calories' => $totalCalories,
        ]);

        foreach ($mealPlanData['meals'] as $meal) {
            // Get full recipe information
            $recipeInfo = $this->spoonacularService->getRecipeInformation($meal['id']);

            MealPlanRecipe::create([
                'meal_plan_id' => $mealPlan->id,
                'spoonacular_recipe_id' => $meal['id'],
                'meal_type' => $this->determineMealType($meal['title']),
                'recipe_title' => $meal['title'],
                'calories' => $recipeInfo['nutrition']['nutrients'][0]['amount'] ?? 0,
                'recipe_data' => $recipeInfo,
            ]);
        }

        return $mealPlan;
    }

    /**
     * Create meal plan from recipe search results.
     */
    protected function createMealPlanFromRecipes(User $user, string $date, array $recipes, int $targetCalories): ?MealPlan
    {
        if (empty($recipes) || isset($recipes['error'])) {
            Log::error('Cannot create meal plan - empty recipes or error', [
                'recipes_count' => count($recipes),
                'has_error' => isset($recipes['error'])
            ]);
            return null;
        }

        // Work with recipes (3 main meals + optional snack)
        $selectedRecipes = array_values($recipes); // Re-index array

        Log::info('Creating meal plan from recipes', [
            'recipe_count' => count($selectedRecipes),
            'recipe_ids' => array_column($selectedRecipes, 'id'),
            'meal_types' => array_column($selectedRecipes, 'meal_type'),
            'user_id' => $user->id,
            'date' => $date
        ]);

        // Translate EACH recipe INDIVIDUALLY to avoid batch translation issues
        for ($recipeIndex = 0; $recipeIndex < count($selectedRecipes); $recipeIndex++) {
            // ALWAYS fetch full recipe data to ensure we have complete instructions
            Log::info("Fetching full recipe information for translation", [
                'recipe_id' => $selectedRecipes[$recipeIndex]['id'],
                'title' => $selectedRecipes[$recipeIndex]['title']
            ]);

            $recipeData = $this->spoonacularService->getRecipeInformation($selectedRecipes[$recipeIndex]['id']);
            $selectedRecipes[$recipeIndex]['full_recipe_data'] = $recipeData;

            // Collect texts to translate for THIS recipe only
            $textsToTranslate = [];
            $textMap = [];

            // 1. Recipe title
            $textsToTranslate[] = $selectedRecipes[$recipeIndex]['title'];
            $textMap['title'] = 0;
            $currentIndex = 1;

            // 2. Ingredient names
            $ingredientIndexes = [];
            if (isset($recipeData['extendedIngredients']) && is_array($recipeData['extendedIngredients'])) {
                foreach ($recipeData['extendedIngredients'] as $idx => $ingredient) {
                    $ingredientName = $ingredient['name'] ?? $ingredient['original'] ?? null;
                    if ($ingredientName) {
                        $textsToTranslate[] = $ingredientName;
                        $ingredientIndexes[$idx] = $currentIndex;
                        $currentIndex++;
                    }
                }
            }

            // 3. Full instructions text
            $instructionsIndex = null;
            if (isset($recipeData['instructions']) && !empty($recipeData['instructions'])) {
                $textsToTranslate[] = $recipeData['instructions'];
                $instructionsIndex = $currentIndex;
            }

            // Translate all texts for this recipe in ONE call
            Log::info("Translating recipe #{$recipeIndex}", [
                'recipe_id' => $selectedRecipes[$recipeIndex]['id'],
                'texts_count' => count($textsToTranslate)
            ]);

            $translated = $this->vertexAIService->translateToPolish($textsToTranslate);

            // Apply translations
            if (count($translated) === count($textsToTranslate)) {
                // Title
                $selectedRecipes[$recipeIndex]['title'] = $translated[$textMap['title']];

                // Ingredients
                if (isset($selectedRecipes[$recipeIndex]['full_recipe_data']['extendedIngredients'])) {
                    foreach ($selectedRecipes[$recipeIndex]['full_recipe_data']['extendedIngredients'] as $idx => &$ingredient) {
                        if (isset($ingredientIndexes[$idx]) && isset($translated[$ingredientIndexes[$idx]])) {
                            $ingredient['name_pl'] = $translated[$ingredientIndexes[$idx]];
                            $ingredient['original_pl'] = $translated[$ingredientIndexes[$idx]];
                        }
                    }
                }

                // Instructions
                if ($instructionsIndex !== null && isset($translated[$instructionsIndex])) {
                    $selectedRecipes[$recipeIndex]['full_recipe_data']['instructions_pl'] = $translated[$instructionsIndex];
                }

                Log::info("Recipe #{$recipeIndex} translated successfully");
            } else {
                Log::warning("Translation mismatch for recipe #{$recipeIndex}, keeping original", [
                    'expected' => count($textsToTranslate),
                    'received' => count($translated)
                ]);
            }
        }

        try {
            $mealPlan = MealPlan::create([
                'user_id' => $user->id,
                'date' => $date,
                'total_calories' => 0,
            ]);

            Log::info('MealPlan created successfully', [
                'meal_plan_id' => $mealPlan->id,
                'user_id' => $user->id,
                'date' => $date
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create MealPlan', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'date' => $date
            ]);
            throw $e;
        }

        $totalCalories = 0;

        foreach ($selectedRecipes as $recipe) {
            // Use the full recipe data with translated ingredients and steps
            $recipeInfo = $recipe['full_recipe_data'] ?? [];

            // Extract calories
            $calories = $recipe['calories'] ?? 0;

            if ($calories == 0 && isset($recipeInfo['nutrition']['nutrients'])) {
                foreach ($recipeInfo['nutrition']['nutrients'] as $nutrient) {
                    if ($nutrient['name'] === 'Calories') {
                        $calories = $nutrient['amount'];
                        break;
                    }
                }
            }

            // Fallback to estimated calories (shouldn't happen with proper API data)
            if ($calories == 0) {
                $calories = round($targetCalories / count($selectedRecipes));
            }

            // Merge original recipe data (with missedIngredients) with full recipe info
            $mergedRecipeData = array_merge($recipeInfo, [
                'missedIngredients' => $recipe['missedIngredients'] ?? [],
                'usedIngredients' => $recipe['usedIngredients'] ?? [],
            ]);

            MealPlanRecipe::create([
                'meal_plan_id' => $mealPlan->id,
                'spoonacular_recipe_id' => $recipe['id'],
                'meal_type' => $recipe['meal_type'] ?? 'snack', // Use meal_type from recipe
                'recipe_title' => $recipe['title'],
                'calories' => $calories,
                'recipe_data' => $mergedRecipeData,
            ]);

            $totalCalories += $calories;
        }

        $mealPlan->update(['total_calories' => $totalCalories]);

        // Validate calorie target - strict tolerance of ±20 kcal
        $calorieDeviation = abs($totalCalories - $targetCalories);
        $deviationPercent = ($calorieDeviation / $targetCalories) * 100;

        if ($calorieDeviation > 20) {
            Log::warning("Meal plan calorie deviation exceeds ±20 kcal tolerance", [
                'target_calories' => $targetCalories,
                'actual_calories' => $totalCalories,
                'deviation' => $calorieDeviation,
                'deviation_percent' => round($deviationPercent, 2),
                'tolerance' => '±20 kcal'
            ]);
        } elseif ($calorieDeviation <= 5) {
            Log::info("Excellent calorie accuracy achieved", [
                'target_calories' => $targetCalories,
                'actual_calories' => $totalCalories,
                'deviation' => $calorieDeviation,
                'deviation_percent' => round($deviationPercent, 2)
            ]);
        }

        return $mealPlan;
    }

    /**
     * Determine meal type from recipe title (simple heuristic).
     */
    protected function determineMealType(string $title): string
    {
        $title = strtolower($title);

        if (str_contains($title, 'breakfast') || str_contains($title, 'pancake') || str_contains($title, 'oatmeal')) {
            return 'breakfast';
        }

        if (str_contains($title, 'lunch') || str_contains($title, 'sandwich') || str_contains($title, 'salad')) {
            return 'lunch';
        }

        if (str_contains($title, 'dinner') || str_contains($title, 'steak') || str_contains($title, 'pasta')) {
            return 'dinner';
        }

        return 'snack';
    }

    /**
     * Filter recipes to only include those within appropriate calorie ranges.
     * This ensures the AI can only select recipes that fit within the daily calorie budget.
     *
     * @param array $recipes
     * @param int $targetCalories
     * @return array
     */
    protected function filterRecipesByCalorieRanges(array $recipes, int $targetCalories): array
    {
        // Use VERY PERMISSIVE filtering - only remove extremely low-calorie recipes
        // We want to give AI maximum flexibility to choose recipes that sum to target
        // Realistic meal ranges:
        // - Breakfast: 400-1000 kcal (16-40% of 2500)
        // - Lunch: 600-1500 kcal (24-60% of 2500)
        // - Dinner: 600-1500 kcal (24-60% of 2500)
        $minCaloriesPerMeal = round($targetCalories * 0.20); // 20% minimum (e.g., 500 for 2500)
        $maxCaloriesPerMeal = round($targetCalories * 0.70); // 70% maximum (e.g., 1750 for 2500) - very permissive!

        $filteredRecipes = array_filter($recipes, function($recipe) use ($minCaloriesPerMeal, $maxCaloriesPerMeal) {
            // Skip recipes without calorie data
            if (!isset($recipe['calories']) || $recipe['calories'] <= 0) {
                return false;
            }

            // Only filter out extremely low or extremely high calorie recipes
            return $recipe['calories'] >= $minCaloriesPerMeal && $recipe['calories'] <= $maxCaloriesPerMeal;
        });

        return array_values($filteredRecipes);
    }

    /**
     * Normalize recipe data from different API sources into unified format.
     * Handles data from: findByIngredients, complexSearch, and getRecipeInformation.
     *
     * @param array $recipe Base recipe data
     * @param array|null $detailedInfo Optional detailed information from getRecipeInformation()
     * @param string $source 'findByIngredients' or 'complexSearch'
     * @return array Normalized recipe structure
     */
    protected function normalizeRecipeData(
        array $recipe,
        ?array $detailedInfo = null,
        string $source = 'complexSearch'
    ): array {
        // Merge base + detailed info
        $merged = $detailedInfo ? array_merge($recipe, $detailedInfo) : $recipe;

        // Extract calories from nutrition data
        $calories = 0;
        if (isset($merged['nutrition']['nutrients'])) {
            foreach ($merged['nutrition']['nutrients'] as $nutrient) {
                if ($nutrient['name'] === 'Calories') {
                    $calories = $nutrient['amount'];
                    break;
                }
            }
        }

        // Determine meal type from recipe metadata
        $mealType = $this->determineMealTypeFromRecipe($merged);

        // Check if has cooking instructions
        $hasInstructions = !empty($merged['analyzedInstructions'])
            || !empty($merged['instructions']);

        return [
            // Basic information
            'id' => $merged['id'],
            'title' => $merged['title'],
            'image' => $merged['image'] ?? null,
            'readyInMinutes' => $merged['readyInMinutes'] ?? null,
            'servings' => $merged['servings'] ?? 2,
            'sourceUrl' => $merged['sourceUrl'] ?? null,

            // Meal type classification
            'meal_type' => $mealType,

            // Ingredients analysis (from findByIngredients)
            'usedIngredients' => $merged['usedIngredients'] ?? [],
            'missedIngredients' => $merged['missedIngredients'] ?? [],
            'usedIngredientCount' => $merged['usedIngredientCount'] ?? count($merged['usedIngredients'] ?? []),
            'missedIngredientCount' => $merged['missedIngredientCount'] ?? count($merged['missedIngredients'] ?? []),

            // Extended ingredients (from getRecipeInformation or complexSearch)
            'extendedIngredients' => $merged['extendedIngredients'] ?? [],

            // Nutrition information
            'calories' => $calories,
            'nutrition' => $merged['nutrition'] ?? [],

            // Cooking instructions
            'analyzedInstructions' => $merged['analyzedInstructions'] ?? [],
            'hasInstructions' => $hasInstructions,

            // Metadata
            'likes' => $merged['likes'] ?? $merged['aggregateLikes'] ?? 0,
            'source' => $source,

            // Full data for database storage
            'full_recipe_data' => $merged,
        ];
    }

    /**
     * Determine meal type from recipe metadata (dishTypes, title, etc.)
     *
     * @param array $recipe Recipe data
     * @return string 'breakfast', 'lunch', 'dinner', or 'snack'
     */
    protected function determineMealTypeFromRecipe(array $recipe): string
    {
        // Check dishTypes first (most reliable source)
        if (isset($recipe['dishTypes']) && is_array($recipe['dishTypes'])) {
            foreach ($recipe['dishTypes'] as $dishType) {
                $dishType = strtolower($dishType);

                if (in_array($dishType, ['breakfast', 'brunch', 'morning meal'])) {
                    return 'breakfast';
                }

                if (in_array($dishType, ['lunch', 'main course', 'main dish'])) {
                    return 'lunch';
                }

                if (in_array($dishType, ['dinner'])) {
                    return 'dinner';
                }

                if (in_array($dishType, ['snack', 'appetizer', 'fingerfood'])) {
                    return 'snack';
                }
            }
        }

        // Fallback to title analysis
        $title = strtolower($recipe['title'] ?? '');

        if (str_contains($title, 'breakfast') || str_contains($title, 'pancake')
            || str_contains($title, 'oatmeal') || str_contains($title, 'smoothie')) {
            return 'breakfast';
        }

        if (str_contains($title, 'dinner') || str_contains($title, 'supper')) {
            return 'dinner';
        }

        // Default to lunch for main courses
        return 'lunch';
    }

    /**
     * Fetch recipes using user's fridge items with full details.
     * Uses complexSearch with includeIngredients for EACH meal type separately.
     * This ensures proper calorie filtering and meal type classification from the API.
     *
     * @param array $fridgeItems User's fridge item names (in Polish)
     * @param array $preferences User preferences
     * @param int $dailyCalories Daily calorie target
     * @return array ['breakfast' => [...], 'lunch' => [...], 'dinner' => [...]]
     */
    protected function fetchRecipesWithFridgeItems(
        array $fridgeItems,
        array $preferences,
        int $dailyCalories
    ): array {
        // Translate Polish ingredient names to English for Spoonacular API
        $ingredientsToTranslate = array_slice($fridgeItems, 0, 10); // Limit to 10 ingredients

        Log::info('Translating fridge items to English', [
            'polish_items' => $ingredientsToTranslate
        ]);

        // Use VertexAI to translate ingredients to English
        $translatedIngredients = $this->vertexAIService->translateIngredientsToEnglish($ingredientsToTranslate);

        if (empty($translatedIngredients)) {
            Log::error('Failed to translate ingredients to English');
            return [];
        }

        Log::info('Translated ingredients', [
            'polish' => $ingredientsToTranslate,
            'english' => $translatedIngredients
        ]);

        // Define meal type configurations with calorie ranges
        $typeMapping = [
            'breakfast' => [
                'type' => 'breakfast',
                'minCalPercent' => 0.20,
                'maxCalPercent' => 0.35,
                'maxReadyTime' => 30,
            ],
            'lunch' => [
                'type' => 'main course,soup',
                'minCalPercent' => 0.30,
                'maxCalPercent' => 0.45,
            ],
            'dinner' => [
                'type' => 'main course,salad,side dish,soup',
                'minCalPercent' => 0.20,
                'maxCalPercent' => 0.35,
            ],
        ];

        $recipesByMealType = [
            'breakfast' => [],
            'lunch' => [],
            'dinner' => [],
        ];

        // Fetch recipes for EACH meal type separately with proper parameters
        foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
            $config = $typeMapping[$mealType];

            // Build parameters for this meal type
            $params = [
                'includeIngredients' => implode(',', $translatedIngredients),
                'type' => $config['type'],
                'minCalories' => round($dailyCalories * $config['minCalPercent']),
                'maxCalories' => round($dailyCalories * $config['maxCalPercent']),
                'number' => 50,
                'addRecipeNutrition' => 'true',  // Must be string
                'addRecipeInformation' => 'true', // Must be string
                'addRecipeInstructions' => 'true', // Must be string
                'fillIngredients' => 'true', // Must be string - shows which fridge items are used/missed
                'sort' => 'max-used-ingredients', // Prioritize recipes using fridge items
            ];

            // Add maxReadyTime for breakfast
            if (isset($config['maxReadyTime'])) {
                $params['maxReadyTime'] = $config['maxReadyTime'];
            }

            // Add diet filter (skip for omnivore)
            if (isset($preferences['diet_type']) && $preferences['diet_type'] !== 'omnivore') {
                $dietMapping = [
                    'vegetarian' => 'vegetarian',
                    'vegan' => 'vegan',
                    'keto' => 'ketogenic',
                ];
                $params['diet'] = $dietMapping[$preferences['diet_type']] ?? null;

                // Add keto-specific constraints
                if ($preferences['diet_type'] === 'keto') {
                    $params['maxCarbs'] = 50;  // Max 50g carbs
                    $params['minFat'] = 20;    // Min 20g fat
                }
            }

            // Add intolerances (allergies)
            if (!empty($preferences['allergies'])) {
                $params['intolerances'] = implode(',', $preferences['allergies']);
            }

            // Add excluded ingredients
            if (!empty($preferences['exclude_ingredients'])) {
                $params['excludeIngredients'] = implode(',', $preferences['exclude_ingredients']);
            }

            Log::info("Fetching {$mealType} recipes with fridge items", [
                'meal_type' => $mealType,
                'fridge_items' => $translatedIngredients,
                'calorie_range' => "{$params['minCalories']}-{$params['maxCalories']} kcal",
                'type' => $params['type']
            ]);

            $result = $this->spoonacularService->complexSearch($params);

            if (isset($result['error']) || !isset($result['results'])) {
                Log::warning("complexSearch with fridge items failed for {$mealType}", [
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                continue;
            }

            // Normalize recipes from complexSearch results
            foreach ($result['results'] as $recipe) {
                $normalized = $this->normalizeRecipeData($recipe, null, 'complexSearch');

                // Skip recipes without calories (critical data)
                if ($normalized['calories'] <= 0) {
                    Log::debug('Skipping recipe without calories', [
                        'id' => $recipe['id'] ?? 'unknown',
                        'title' => $recipe['title'] ?? 'unknown',
                    ]);
                    continue;
                }

                // Override meal type with our classification
                $normalized['meal_type'] = $mealType;

                $recipesByMealType[$mealType][] = $normalized;
            }

            Log::info("Fetched {$mealType} recipes with fridge items", [
                'meal_type' => $mealType,
                'count' => count($recipesByMealType[$mealType]),
                'avg_calories' => count($recipesByMealType[$mealType]) > 0
                    ? round(array_sum(array_column($recipesByMealType[$mealType], 'calories')) / count($recipesByMealType[$mealType]))
                    : 0
            ]);
        }

        return $recipesByMealType;
    }

    /**
     * Fetch recipes using complexSearch for each meal type separately.
     * Used when user has no fridge items.
     *
     * @param array $preferences User preferences
     * @param int $dailyCalories Daily calorie target
     * @return array ['breakfast' => [...], 'lunch' => [...], 'dinner' => [...]]
     */
    protected function fetchRecipesByMealTypes(
        array $preferences,
        int $dailyCalories
    ): array {
        $recipesByMealType = [
            'breakfast' => [],
            'lunch' => [],
            'dinner' => [],
        ];

        foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
            $result = $this->spoonacularService->complexSearchByMealType(
                $mealType,
                $dailyCalories,
                $preferences
            );

            if (isset($result['error']) || !isset($result['results'])) {
                Log::error("complexSearch failed for {$mealType}", [
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                continue;
            }

            // Normalize recipes from complexSearch results
            foreach ($result['results'] as $recipe) {
                $normalized = $this->normalizeRecipeData($recipe, null, 'complexSearch');

                // Skip recipes without calories
                if ($normalized['calories'] <= 0) {
                    continue;
                }

                // Override meal type with our specific classification
                $normalized['meal_type'] = $mealType;

                $recipesByMealType[$mealType][] = $normalized;
            }

            Log::info("complexSearch returned recipes for {$mealType}", [
                'count' => count($recipesByMealType[$mealType]),
                'avg_calories' => count($recipesByMealType[$mealType]) > 0
                    ? round(array_sum(array_column($recipesByMealType[$mealType], 'calories')) / count($recipesByMealType[$mealType]))
                    : 0
            ]);
        }

        return $recipesByMealType;
    }

    /**
     * Filter recipes for a specific meal type and calorie range.
     * Used when processing recipes from findByIngredients that need meal type classification.
     *
     * @param array $recipes All recipes
     * @param string $mealType 'breakfast', 'lunch', 'dinner'
     * @param int $dailyCalories Daily calorie target
     * @return array Filtered recipes
     */
    protected function filterRecipesByMealType(
        array $recipes,
        string $mealType,
        int $dailyCalories
    ): array {
        // Define calorie ranges per meal type
        $ranges = [
            'breakfast' => ['min' => 0.20, 'max' => 0.35],
            'lunch' => ['min' => 0.30, 'max' => 0.45],
            'dinner' => ['min' => 0.20, 'max' => 0.35],
        ];

        $range = $ranges[$mealType] ?? $ranges['lunch'];
        $minCal = round($dailyCalories * $range['min']);
        $maxCal = round($dailyCalories * $range['max']);

        // Define allowed dish types per meal
        $dishTypes = [
            'breakfast' => ['breakfast', 'brunch', 'morning meal'],
            'lunch' => ['main course', 'soup', 'salad', 'main dish'],
            'dinner' => ['main course', 'salad', 'side dish', 'soup', 'main dish'],
        ];

        $allowedDishTypes = $dishTypes[$mealType] ?? [];

        // First pass: strict filtering (calorie range + dish types)
        $strictFiltered = array_filter($recipes, function($recipe) use ($minCal, $maxCal, $allowedDishTypes) {
            // Check calorie range
            if ($recipe['calories'] < $minCal || $recipe['calories'] > $maxCal) {
                return false;
            }

            // Check dish types if available in recipe data
            if (isset($recipe['full_recipe_data']['dishTypes'])) {
                $recipeDishTypes = array_map('strtolower', $recipe['full_recipe_data']['dishTypes']);
                $matches = array_intersect($recipeDishTypes, $allowedDishTypes);

                if (empty($matches)) {
                    return false;
                }
            }

            return true;
        });

        // If strict filtering gives us enough recipes (at least 5), use those
        if (count($strictFiltered) >= 5) {
            Log::info("Strict filtering succeeded for {$mealType}", [
                'meal_type' => $mealType,
                'count' => count($strictFiltered)
            ]);
            return array_values($strictFiltered);
        }

        // Second pass: lenient filtering (calorie range only)
        Log::info("Strict filtering insufficient for {$mealType}, using lenient filtering", [
            'meal_type' => $mealType,
            'strict_count' => count($strictFiltered)
        ]);

        $lenientFiltered = array_filter($recipes, function($recipe) use ($minCal, $maxCal) {
            // Only check calorie range, ignore dish types
            return $recipe['calories'] >= $minCal && $recipe['calories'] <= $maxCal;
        });

        Log::info("Lenient filtering result for {$mealType}", [
            'meal_type' => $mealType,
            'count' => count($lenientFiltered),
            'calorie_range' => "{$minCal}-{$maxCal} kcal"
        ]);

        return array_values($lenientFiltered);
    }

    /**
     * Find a snack recipe to fill calorie deficit.
     *
     * @param int $targetCalories Calorie deficit to fill
     * @param array $preferences User preferences
     * @param array $fridgeItems Available fridge items
     * @return array|null Snack recipe or null if not found
     */
    protected function findSnackToFillCalories(int $targetCalories, array $preferences, array $fridgeItems = []): ?array
    {
        try {
            // Build snack search parameters
            $params = [
                'type' => 'snack,appetizer,dessert,beverage',
                'minCalories' => max(50, $targetCalories - 50),  // Allow ±50 kcal tolerance
                'maxCalories' => $targetCalories + 50,
                'number' => 20,
                'addRecipeNutrition' => 'true',
                'addRecipeInformation' => 'true',
                'sort' => 'random',
            ];

            // Add diet filter
            if (isset($preferences['diet_type']) && $preferences['diet_type'] !== 'omnivore') {
                $dietMapping = [
                    'vegetarian' => 'vegetarian',
                    'vegan' => 'vegan',
                    'keto' => 'ketogenic',
                ];
                $params['diet'] = $dietMapping[$preferences['diet_type']] ?? null;
            }

            // Add allergies
            if (!empty($preferences['allergies'])) {
                $params['intolerances'] = implode(',', $preferences['allergies']);
            }

            // Add excluded ingredients
            if (!empty($preferences['exclude_ingredients'])) {
                $params['excludeIngredients'] = implode(',', $preferences['exclude_ingredients']);
            }

            // Try with fridge items first if available
            if (!empty($fridgeItems)) {
                $translatedIngredients = $this->vertexAIService->translateIngredientsToEnglish(array_slice($fridgeItems, 0, 5));
                if (!empty($translatedIngredients)) {
                    $params['includeIngredients'] = implode(',', $translatedIngredients);
                }
            }

            Log::info('Searching for snack to fill calorie deficit', [
                'target_calories' => $targetCalories,
                'search_range' => "{$params['minCalories']}-{$params['maxCalories']} kcal"
            ]);

            $result = $this->spoonacularService->complexSearch($params);

            if (isset($result['error']) || !isset($result['results']) || empty($result['results'])) {
                Log::warning('No snacks found');
                return null;
            }

            // Normalize and filter snacks
            $snacks = [];
            foreach ($result['results'] as $recipe) {
                $normalized = $this->normalizeRecipeData($recipe, null, 'complexSearch');

                // Skip recipes without calories
                if ($normalized['calories'] <= 0) {
                    continue;
                }

                // Prefer snacks closer to target
                $normalized['calorie_distance'] = abs($normalized['calories'] - $targetCalories);
                $snacks[] = $normalized;
            }

            if (empty($snacks)) {
                Log::warning('No valid snacks found after filtering');
                return null;
            }

            // Sort by calorie distance (closest to target first)
            usort($snacks, fn($a, $b) => $a['calorie_distance'] <=> $b['calorie_distance']);

            // Return best match
            return $snacks[0];

        } catch (\Exception $e) {
            Log::error('Failed to find snack', ['error' => $e->getMessage()]);
            return null;
        }
    }

}
