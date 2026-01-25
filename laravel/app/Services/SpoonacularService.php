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
                'number' => 10,
                'ranking' => 2, // Maximize used ingredients
                'ignorePantry' => true,
            ];

            // Add diet filter if specified
            if (isset($preferences['diet_type']) && $preferences['diet_type'] !== 'omnivore') {
                $params['diet'] = $preferences['diet_type'];
            }

            // Add intolerances (allergies)
            if (isset($preferences['allergies']) && !empty($preferences['allergies'])) {
                $params['intolerances'] = implode(',', $preferences['allergies']);
            }

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
     * Get detailed recipe information.
     *
     * @param int $recipeId
     * @return array
     */
    public function getRecipeInformation(int $recipeId): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/recipes/{$recipeId}/information", [
                'apiKey' => $this->apiKey,
                'includeNutrition' => 'true',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'Failed to fetch recipe information'];
        } catch (\Exception $e) {
            Log::error('Spoonacular Recipe Info Error: ' . $e->getMessage());
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
                'addRecipeInformation' => true,
                'fillIngredients' => true,
                'addRecipeNutrition' => true,
                'instructionsRequired' => true,
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
}
