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
            $targetCalories = $preferences->daily_calories ?? 2000;

            // Get user's fridge items
            $fridgeItems = $user->fridgeItems()->pluck('product_name')->toArray();

            // Prepare preferences array for API
            $preferencesArray = [
                'diet_type' => $preferences->diet_type ?? 'omnivore',
                'allergies' => $preferences->allergies ?? [],
                'exclude_ingredients' => $preferences->exclude_ingredients ?? [],
                'daily_calories' => $targetCalories,
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

            // If user has fridge items, search for recipes using those ingredients
            if (!empty($fridgeItems)) {
                $recipes = $this->spoonacularService->searchRecipesByIngredients(
                    $fridgeItems,
                    $preferencesArray
                );

                if (isset($recipes['error']) || empty($recipes)) {
                    Log::error('Failed to fetch recipes: ' . ($recipes['error'] ?? 'empty result'));

                    // If it's an API limit error, return it with flag
                    if (isset($recipes['api_limit']) && $recipes['api_limit']) {
                        return [
                            'error' => $recipes['error'],
                            'api_limit' => true
                        ];
                    }

                    return null;
                }

                // Shuffle for variety and enrich more recipes for better selection
                shuffle($recipes);
                $enrichedRecipes = $this->enrichRecipesWithNutrition(array_slice($recipes, 0, 30));

                if (empty($enrichedRecipes)) {
                    Log::error('Failed to enrich recipes with nutrition data');
                    return null;
                }

                // Filter recipes by calorie ranges to ensure they fit within daily budget
                $filteredRecipes = $this->filterRecipesByCalorieRanges($enrichedRecipes, $targetCalories);

                if (empty($filteredRecipes)) {
                    Log::warning('No recipes found within calorie ranges, using all enriched recipes');
                    $filteredRecipes = $enrichedRecipes;
                }

                // Use VertexAI to select the best 3 recipes
                $selectedRecipeIds = $this->vertexAIService->selectBestRecipes(
                    $filteredRecipes,
                    $preferencesArray,
                    $recentRecipeIds,
                    $fridgeItems
                );

                if (empty($selectedRecipeIds)) {
                    Log::error('Failed to select recipes');
                    return null;
                }

                // Filter recipes to only selected ones
                $selectedRecipes = array_filter($enrichedRecipes, function($recipe) use ($selectedRecipeIds) {
                    return in_array($recipe['id'], $selectedRecipeIds);
                });

                // Reorder recipes according to AI selection (breakfast, lunch, dinner)
                $orderedRecipes = [];
                foreach ($selectedRecipeIds as $id) {
                    foreach ($selectedRecipes as $recipe) {
                        if ($recipe['id'] === $id) {
                            $orderedRecipes[] = $recipe;
                            break;
                        }
                    }
                }

                return $this->createMealPlanFromRecipes($user, $date, $orderedRecipes, $targetCalories);
            } else {
                // No fridge items - use complex search with nutrition data
                $recipes = $this->spoonacularService->complexSearch([
                    'diet' => $preferencesArray['diet_type'] !== 'omnivore' ? $preferencesArray['diet_type'] : null,
                    'intolerances' => !empty($preferencesArray['allergies']) ? implode(',', $preferencesArray['allergies']) : null,
                    'number' => 30,
                    'addRecipeNutrition' => true,
                    'addRecipeInformation' => true,
                    'sort' => 'random',
                    'offset' => rand(0, 100), // Add randomization to avoid same results
                ]);

                if (isset($recipes['error']) || !isset($recipes['results'])) {
                    Log::error('Failed to fetch recipes via complex search');

                    // If it's an API limit error, return it with flag
                    if (isset($recipes['api_limit']) && $recipes['api_limit']) {
                        return [
                            'error' => $recipes['error'],
                            'api_limit' => true
                        ];
                    }

                    return null;
                }

                // Transform recipes to standard format with nutrition
                $formattedRecipes = array_map(function($recipe) {
                    $calories = 0;
                    if (isset($recipe['nutrition']['nutrients'])) {
                        foreach ($recipe['nutrition']['nutrients'] as $nutrient) {
                            if ($nutrient['name'] === 'Calories') {
                                $calories = $nutrient['amount'];
                                break;
                            }
                        }
                    }

                    return [
                        'id' => $recipe['id'],
                        'title' => $recipe['title'],
                        'usedIngredients' => [],
                        'missedIngredients' => [],
                        'likes' => $recipe['aggregateLikes'] ?? 0,
                        'calories' => $calories,
                        'nutrition' => $recipe['nutrition'] ?? [],
                        'full_recipe_data' => $recipe, // Store complete recipe from complexSearch
                    ];
                }, $recipes['results']);

                // Filter recipes by calorie ranges to ensure they fit within daily budget
                $filteredRecipes = $this->filterRecipesByCalorieRanges($formattedRecipes, $targetCalories);

                if (empty($filteredRecipes)) {
                    Log::warning('No recipes found within calorie ranges, using all formatted recipes');
                    $filteredRecipes = $formattedRecipes;
                }

                // Use VertexAI to select best recipes
                $selectedRecipeIds = $this->vertexAIService->selectBestRecipes(
                    $filteredRecipes,
                    $preferencesArray,
                    $recentRecipeIds,
                    []
                );

                if (empty($selectedRecipeIds)) {
                    // Fallback to random 3 recipes for variety
                    $shuffled = $formattedRecipes;
                    shuffle($shuffled);
                    $selectedRecipeIds = array_slice(array_column($shuffled, 'id'), 0, 3);
                }

                $selectedRecipes = array_filter($formattedRecipes, function($recipe) use ($selectedRecipeIds) {
                    return in_array($recipe['id'], $selectedRecipeIds);
                });

                return $this->createMealPlanFromRecipes($user, $date, $selectedRecipes, $targetCalories);
            }
        } catch (\Exception $e) {
            Log::error('Meal Planner Error: ' . $e->getMessage());
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
            return null;
        }

        // Work with first 3 recipes
        $selectedRecipes = array_slice($recipes, 0, 3);
        $selectedRecipes = array_values($selectedRecipes); // Re-index array

        // Translate recipe titles from English to Polish using VertexAI
        $recipeTitles = array_map(fn($recipe) => $recipe['title'], $selectedRecipes);
        $translatedTitles = $this->vertexAIService->translateToPolish($recipeTitles);

        // Collect all unique ingredients and steps from all recipes for translation
        $allIngredients = [];
        $allSteps = [];
        $recipeIngredientMaps = []; // Store which ingredients belong to which recipe
        $recipeStepMaps = []; // Store which steps belong to which recipe

        for ($recipeIndex = 0; $recipeIndex < count($selectedRecipes); $recipeIndex++) {
            // Update title
            if (isset($translatedTitles[$recipeIndex])) {
                $selectedRecipes[$recipeIndex]['title'] = $translatedTitles[$recipeIndex];
            }

            // Ensure recipe has full data
            $recipeData = $selectedRecipes[$recipeIndex]['full_recipe_data'] ?? null;
            if (!$recipeData) {
                $recipeData = $this->spoonacularService->getRecipeInformation($selectedRecipes[$recipeIndex]['id']);
                $selectedRecipes[$recipeIndex]['full_recipe_data'] = $recipeData;
            }

            // Extract ingredient names for translation
            $recipeIngredientMaps[$recipeIndex] = [];
            if (isset($recipeData['extendedIngredients']) && is_array($recipeData['extendedIngredients'])) {
                foreach ($recipeData['extendedIngredients'] as $ingredient) {
                    $ingredientName = $ingredient['name'] ?? $ingredient['original'] ?? null;
                    if ($ingredientName) {
                        $allIngredients[] = $ingredientName;
                        $recipeIngredientMaps[$recipeIndex][] = count($allIngredients) - 1; // Store index
                    }
                }
            }

            // Extract steps for translation
            $recipeStepMaps[$recipeIndex] = [];
            if (isset($recipeData['analyzedInstructions'][0]['steps']) && is_array($recipeData['analyzedInstructions'][0]['steps'])) {
                foreach ($recipeData['analyzedInstructions'][0]['steps'] as $step) {
                    if (isset($step['step'])) {
                        $allSteps[] = $step['step'];
                        $recipeStepMaps[$recipeIndex][] = count($allSteps) - 1; // Store index
                    }
                }
            }
        }

        // Translate all ingredients and steps at once (more efficient)
        $translatedIngredients = [];
        if (!empty($allIngredients)) {
            $translatedIngredients = $this->vertexAIService->translateToPolish($allIngredients);
        }

        $translatedSteps = [];
        if (!empty($allSteps)) {
            $translatedSteps = $this->vertexAIService->translateToPolish($allSteps);
        }

        // Add Polish translations to each recipe's ingredient and step data
        for ($recipeIndex = 0; $recipeIndex < count($selectedRecipes); $recipeIndex++) {
            // Translate ingredients
            if (isset($selectedRecipes[$recipeIndex]['full_recipe_data']['extendedIngredients'])) {
                $ingredientIndexes = $recipeIngredientMaps[$recipeIndex] ?? [];

                foreach ($selectedRecipes[$recipeIndex]['full_recipe_data']['extendedIngredients'] as $ingredientIndex => &$ingredient) {
                    if (isset($ingredientIndexes[$ingredientIndex])) {
                        $globalIndex = $ingredientIndexes[$ingredientIndex];
                        // Add Polish translation to ingredient data
                        $ingredient['name_pl'] = $translatedIngredients[$globalIndex] ?? $ingredient['name'];
                        $ingredient['original_pl'] = $translatedIngredients[$globalIndex] ?? $ingredient['original'];
                    }
                }
            }

            // Translate cooking steps
            if (isset($selectedRecipes[$recipeIndex]['full_recipe_data']['analyzedInstructions'][0]['steps'])) {
                $stepIndexes = $recipeStepMaps[$recipeIndex] ?? [];

                foreach ($selectedRecipes[$recipeIndex]['full_recipe_data']['analyzedInstructions'][0]['steps'] as $stepIndex => &$step) {
                    if (isset($stepIndexes[$stepIndex])) {
                        $globalIndex = $stepIndexes[$stepIndex];
                        // Add Polish translation to step data
                        $step['step_pl'] = $translatedSteps[$globalIndex] ?? $step['step'];
                    }
                }
            }
        }

        $mealPlan = MealPlan::create([
            'user_id' => $user->id,
            'date' => $date,
            'total_calories' => 0,
        ]);

        $totalCalories = 0;
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        $caloriesPerMeal = $targetCalories / 3;

        foreach ($selectedRecipes as $index => $recipe) {
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

            // Fallback to estimated calories
            if ($calories == 0) {
                $calories = $caloriesPerMeal;
            }

            // Merge original recipe data (with missedIngredients) with full recipe info
            $mergedRecipeData = array_merge($recipeInfo, [
                'missedIngredients' => $recipe['missedIngredients'] ?? [],
                'usedIngredients' => $recipe['usedIngredients'] ?? [],
            ]);

            MealPlanRecipe::create([
                'meal_plan_id' => $mealPlan->id,
                'spoonacular_recipe_id' => $recipe['id'],
                'meal_type' => $mealTypes[$index] ?? 'snack',
                'recipe_title' => $recipe['title'],
                'calories' => $calories,
                'recipe_data' => $mergedRecipeData,
            ]);

            $totalCalories += $calories;
        }

        $mealPlan->update(['total_calories' => $totalCalories]);

        // Validate calorie target
        $calorieDeviation = abs($totalCalories - $targetCalories);
        $deviationPercent = ($calorieDeviation / $targetCalories) * 100;

        if ($deviationPercent > 20) {
            Log::warning("Meal plan calorie deviation exceeds 20%", [
                'target_calories' => $targetCalories,
                'actual_calories' => $totalCalories,
                'deviation' => $calorieDeviation,
                'deviation_percent' => round($deviationPercent, 1)
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
        // Define acceptable calorie ranges - wide enough for variety but eliminate extremes
        // Realistic meal ranges:
        // - Breakfast: 300-900 kcal (12-36% of 2500)
        // - Lunch: 400-1100 kcal (16-44% of 2500)
        // - Dinner: 400-1100 kcal (16-44% of 2500)
        $minCaloriesPerMeal = round($targetCalories * 0.15); // 15% minimum
        $maxCaloriesPerMeal = round($targetCalories * 0.50); // 50% maximum

        $filteredRecipes = array_filter($recipes, function($recipe) use ($minCaloriesPerMeal, $maxCaloriesPerMeal) {
            // Skip recipes without calorie data
            if (!isset($recipe['calories']) || $recipe['calories'] <= 0) {
                return false;
            }

            // Only include recipes within the acceptable range
            return $recipe['calories'] >= $minCaloriesPerMeal && $recipe['calories'] <= $maxCaloriesPerMeal;
        });

        return array_values($filteredRecipes);
    }

    /**
     * Enrich recipes with nutrition information from Spoonacular API.
     *
     * @param array $recipes
     * @return array
     */
    protected function enrichRecipesWithNutrition(array $recipes): array
    {
        $enrichedRecipes = [];

        foreach ($recipes as $recipe) {
            try {
                // Get full recipe information including nutrition
                $recipeInfo = $this->spoonacularService->getRecipeInformation($recipe['id']);

                if (isset($recipeInfo['error'])) {
                    Log::warning("Failed to get nutrition for recipe {$recipe['id']}");
                    continue;
                }

                // Extract calories from nutrition data
                $calories = 0;
                if (isset($recipeInfo['nutrition']['nutrients'])) {
                    foreach ($recipeInfo['nutrition']['nutrients'] as $nutrient) {
                        if ($nutrient['name'] === 'Calories') {
                            $calories = $nutrient['amount'];
                            break;
                        }
                    }
                }

                // Merge original recipe data with full recipe info and extracted calories
                $recipe['calories'] = $calories;
                $recipe['nutrition'] = $recipeInfo['nutrition'] ?? [];
                $recipe['full_recipe_data'] = $recipeInfo; // Store complete recipe info

                $enrichedRecipes[] = $recipe;

            } catch (\Exception $e) {
                Log::error("Error enriching recipe {$recipe['id']}: " . $e->getMessage());
                continue;
            }
        }

        return $enrichedRecipes;
    }
}
