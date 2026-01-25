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

                // Enrich recipes with nutrition information (for top 15 recipes to optimize API calls)
                $enrichedRecipes = $this->enrichRecipesWithNutrition(array_slice($recipes, 0, 15));

                if (empty($enrichedRecipes)) {
                    Log::error('Failed to enrich recipes with nutrition data');
                    return null;
                }

                // Use VertexAI to select the best 3 recipes
                $selectedRecipeIds = $this->vertexAIService->selectBestRecipes(
                    $enrichedRecipes,
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
                    'number' => 15,
                    'addRecipeNutrition' => true,
                    'addRecipeInformation' => true,
                    'sort' => 'random',
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

                // Use VertexAI to select best recipes
                $selectedRecipeIds = $this->vertexAIService->selectBestRecipes(
                    $formattedRecipes,
                    $preferencesArray,
                    $recentRecipeIds,
                    []
                );

                if (empty($selectedRecipeIds)) {
                    // Fallback to first 3 recipes
                    $selectedRecipeIds = array_slice(array_column($formattedRecipes, 'id'), 0, 3);
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

        // Translate recipe titles from English to Polish using VertexAI
        $recipeTitles = array_map(fn($recipe) => $recipe['title'], array_slice($recipes, 0, 3));
        $translatedTitles = $this->vertexAIService->translateToPolish($recipeTitles);

        // Replace titles with translated versions
        foreach (array_slice($recipes, 0, 3) as $index => &$recipe) {
            if (isset($translatedTitles[$index])) {
                $recipe['title'] = $translatedTitles[$index];
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

        foreach (array_slice($recipes, 0, 3) as $index => $recipe) {
            // Check if recipe already has full nutrition data
            if (isset($recipe['calories']) && isset($recipe['full_recipe_data'])) {
                // Use existing full recipe data
                $calories = $recipe['calories'];
                $recipeInfo = $recipe['full_recipe_data'];
            } else {
                // Fetch full recipe information from API
                $recipeInfo = $this->spoonacularService->getRecipeInformation($recipe['id']);

                // Extract calories
                $calories = 0;
                if (isset($recipeInfo['nutrition']['nutrients'])) {
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
            }

            MealPlanRecipe::create([
                'meal_plan_id' => $mealPlan->id,
                'spoonacular_recipe_id' => $recipe['id'],
                'meal_type' => $mealTypes[$index] ?? 'snack',
                'recipe_title' => $recipe['title'],
                'calories' => $calories,
                'recipe_data' => $recipeInfo,
            ]);

            $totalCalories += $calories;
        }

        $mealPlan->update(['total_calories' => $totalCalories]);

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
