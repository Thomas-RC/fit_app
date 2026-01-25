<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpoonacularService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.spoonacular.com';

    public function __construct()
    {
        $this->apiKey = AppSetting::get('spoonacular_api_key');
    }

    /**
     * Search for recipes based on ingredients.
     * NOTE: This endpoint does NOT support 'type' or 'diet' parameters.
     * Use complexSearch for filtering by diet/type.
     *
     * @param array $ingredients
     * @param array $preferences
     * @return array
     */
    public function searchRecipesByIngredients(array $ingredients, array $preferences = []): array
    {
        try {
            $params = [
                'apiKey' => $this->apiKey,
                'ingredients' => implode(',', $ingredients),
                'number' => 300, // Increased to 300 for maximum variety
                'ranking' => 2, // Maximize used ingredients
                'ignorePantry' => true,
            ];

            // NOTE: 'type' and 'diet' parameters are NOT supported by findByIngredients endpoint
            // Filtering will be done locally after fetching detailed recipe information

            $response = Http::get("{$this->baseUrl}/recipes/findByIngredients", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Spoonacular API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => '/recipes/findByIngredients',
                'params' => array_merge($params, ['apiKey' => '***'])
            ]);

            // Check if it's a 402 Payment Required (API limit exceeded)
            if ($response->status() === 402) {
                return [
                    'error' => 'API daily limit exceeded',
                    'status_code' => 402,
                    'api_limit' => true
                ];
            }

            return [
                'error' => 'Failed to fetch recipes',
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Spoonacular API Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get detailed recipe information with full nutrition data.
     * ALWAYS includes nutrition information.
     *
     * @param int $recipeId
     * @return array
     */
    public function getRecipeInformation(int $recipeId): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/recipes/{$recipeId}/information", [
                'apiKey' => $this->apiKey,
                'includeNutrition' => 'true', // ALWAYS include nutrition
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Spoonacular Recipe Info Error', [
                'recipe_id' => $recipeId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'Failed to fetch recipe information'];
        } catch (\Exception $e) {
            Log::error('Spoonacular Recipe Info Exception: ' . $e->getMessage(), [
                'recipe_id' => $recipeId,
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate a meal plan for a day using random recipes.
     *
     * @param int $targetCalories
     * @param array $preferences
     * @return array
     */
    public function generateDayMealPlan(int $targetCalories, array $preferences = []): array
    {
        try {
            // Get random recipes (simple and cost-effective)
            $params = [
                'apiKey' => $this->apiKey,
                'number' => 3,
            ];

            // Add tags for diet filter
            $tags = [];
            if (isset($preferences['diet_type']) && $preferences['diet_type'] !== 'omnivore') {
                $tags[] = $preferences['diet_type'];
            }
            if (!empty($tags)) {
                $params['tags'] = implode(',', $tags);
            }

            $response = Http::get("{$this->baseUrl}/recipes/random", $params);

            if ($response->successful()) {
                $data = $response->json();

                // Transform to meal plan format
                if (isset($data['recipes'])) {
                    $meals = [];
                    $totalCalories = 0;

                    foreach ($data['recipes'] as $recipe) {
                        $meals[] = [
                            'id' => $recipe['id'],
                            'title' => $recipe['title'],
                            'readyInMinutes' => $recipe['readyInMinutes'] ?? 30,
                            'servings' => $recipe['servings'] ?? 2,
                        ];
                    }

                    return [
                        'meals' => $meals,
                        'nutrients' => [
                            'calories' => $totalCalories,
                        ],
                    ];
                }
            }

            return ['error' => 'Failed to generate meal plan'];
        } catch (\Exception $e) {
            Log::error('Spoonacular Meal Plan Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get random recipes based on preferences.
     *
     * @param array $preferences
     * @param int $number
     * @return array
     */
    public function getRandomRecipes(array $preferences = [], int $number = 10): array
    {
        try {
            $params = [
                'apiKey' => $this->apiKey,
                'number' => $number,
            ];

            if (isset($preferences['diet_type']) && $preferences['diet_type'] !== 'omnivore') {
                $params['tags'] = $preferences['diet_type'];
            }

            $response = Http::get("{$this->baseUrl}/recipes/random", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Spoonacular Random Recipes Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Check if it's a 402 Payment Required (API limit exceeded)
            if ($response->status() === 402) {
                return [
                    'error' => 'API daily limit exceeded',
                    'status_code' => 402,
                    'api_limit' => true
                ];
            }

            return [
                'error' => 'Failed to fetch random recipes',
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Spoonacular Random Recipes Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Complex recipe search with filters.
     *
     * @param array $params
     * @return array
     */
    public function complexSearch(array $params = []): array
    {
        try {
            $defaults = [
                'apiKey' => $this->apiKey,
                'number' => 12,
                'addRecipeInformation' => 'true',  // Must be string 'true', not boolean
                'fillIngredients' => 'true',       // Must be string 'true', not boolean
                'addRecipeNutrition' => 'true',    // Must be string 'true', not boolean
                'addRecipeInstructions' => 'true', // Must be string 'true', not boolean
                // NOTE: instructionsRequired is NOT reliable in Spoonacular API - validate manually instead
                'sort' => 'popularity',
            ];

            $params = array_merge($defaults, $params);

            $response = Http::get("{$this->baseUrl}/recipes/complexSearch", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Spoonacular Complex Search Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Check if it's a 402 Payment Required (API limit exceeded)
            if ($response->status() === 402) {
                return [
                    'error' => 'API daily limit exceeded',
                    'status_code' => 402,
                    'api_limit' => true
                ];
            }

            return [
                'error' => 'Failed to search recipes',
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Spoonacular Complex Search Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Complex search specifically for a meal type with calorie constraints.
     * Uses optimized parameters for breakfast, lunch, or dinner.
     *
     * @param string $mealType 'breakfast', 'lunch', 'dinner'
     * @param int $dailyCalories User's daily calorie target
     * @param array $preferences User preferences (diet_type, allergies, exclude_ingredients)
     * @return array
     */
    public function complexSearchByMealType(
        string $mealType,
        int $dailyCalories,
        array $preferences = []
    ): array {
        // Define meal type configurations
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

        $config = $typeMapping[$mealType] ?? $typeMapping['lunch'];

        $params = [
            'type' => $config['type'],
            'minCalories' => round($dailyCalories * $config['minCalPercent']),
            'maxCalories' => round($dailyCalories * $config['maxCalPercent']),
            'number' => 50,
            'addRecipeNutrition' => 'true',      // Must be string
            'addRecipeInformation' => 'true',    // Must be string
            'addRecipeInstructions' => 'true',   // Must be string
            'sort' => 'random',
            'offset' => rand(0, 100), // Randomization for variety
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
                $params['maxCarbs'] = 50;  // Max 50g carbs per recipe
                $params['minFat'] = 20;    // Min 20g fat per recipe
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

        Log::info("ComplexSearch by meal type: {$mealType}", [
            'params' => array_filter($params, fn($v) => $v !== null && $v !== ''),
        ]);

        return $this->complexSearch($params);
    }
}
