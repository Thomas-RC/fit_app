<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpoonacularService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.spoonacular.com';
    protected $vertexAIService;

    public function __construct(VertexAIService $vertexAIService)
    {
        $this->apiKey = AppSetting::get('spoonacular_api_key');
        $this->vertexAIService = $vertexAIService;
    }

    /**
     * Get detailed recipe information with full nutrition data.
     * RESERVED FOR FUTURE USE: Will be used to fetch nutrition data for ingredients.
     *
     * @param int $recipeId
     * @return array
     */
    public function getRecipeInformation(int $recipeId): array
    {
        try {
            $params = [
                'apiKey' => $this->apiKey,
                'includeNutrition' => true, // ALWAYS include nutrition
            ];

            $response = Http::get("{$this->baseUrl}/recipes/{$recipeId}/information", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Spoonacular API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => "/recipes/{$recipeId}/information"
            ]);

            if ($response->status() === 402) {
                return [
                    'error' => 'API daily limit exceeded',
                    'status_code' => 402,
                    'api_limit' => true
                ];
            }

            return [
                'error' => 'Failed to fetch recipe',
                'status_code' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Spoonacular API Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Complex search with various filters.
     * RESERVED FOR FUTURE USE: May be used to search recipes for nutrition data.
     *
     * @param array $params
     * @return array
     */
    public function complexSearch(array $params = []): array
    {
        try {
            $params['apiKey'] = $this->apiKey;
            $params['addRecipeInformation'] = true;
            $params['fillIngredients'] = true;
            $params['instructionsRequired'] = false;

            $response = Http::get("{$this->baseUrl}/recipes/complexSearch", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Spoonacular Complex Search Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'params' => array_merge($params, ['apiKey' => '***'])
            ]);

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

    // ========================================
    // Nutrition Data Methods (Ingredients API)
    // ========================================

    /**
     * Search for an ingredient by name.
     * Returns the first matching ingredient with ID and basic info.
     *
     * @param string $query Ingredient name to search for
     * @return array|null First matching ingredient or null if not found
     */
    public function searchIngredient(string $query): ?array
    {
        try {
            $params = [
                'apiKey' => $this->apiKey,
                'query' => $query,
                'metaInformation' => true, // Include basic nutrition
                'number' => 1, // Only need first result
            ];

            $response = Http::get("{$this->baseUrl}/food/ingredients/search", $params);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['results'])) {
                    return $data['results'][0]; // Return first match
                }

                Log::warning('No ingredient found', ['query' => $query]);
                return null;
            }

            Log::error('Spoonacular Ingredient Search Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'query' => $query
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Spoonacular Ingredient Search Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detailed nutrition information for a specific ingredient.
     *
     * @param int $ingredientId Spoonacular ingredient ID
     * @param float $amount Amount of ingredient (default: 100)
     * @param string $unit Unit of measurement (default: grams)
     * @return array|null Nutrition data or null if failed
     */
    public function getIngredientInformation(int $ingredientId, float $amount = 100, string $unit = 'grams'): ?array
    {
        try {
            $params = [
                'apiKey' => $this->apiKey,
                'amount' => $amount,
                'unit' => $unit,
            ];

            $response = Http::get("{$this->baseUrl}/food/ingredients/{$ingredientId}/information", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Spoonacular Ingredient Information Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'ingredient_id' => $ingredientId
            ]);

            if ($response->status() === 402) {
                return [
                    'error' => 'API daily limit exceeded',
                    'status_code' => 402,
                    'api_limit' => true
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Spoonacular Ingredient Information Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Enrich a fridge item with nutrition data from Spoonacular.
     * Searches for ingredient, fetches nutrition, and updates the model.
     *
     * @param \App\Models\FridgeItem $item
     * @return bool Success status
     */
    public function enrichFridgeItemWithNutrition(\App\Models\FridgeItem $item): bool
    {
        try {
            Log::info('Enriching fridge item with nutrition', [
                'item_id' => $item->id,
                'product_name' => $item->product_name
            ]);

            // Translate Polish name to English for Spoonacular
            $englishName = $this->vertexAIService->translateProductName($item->product_name);

            if (!$englishName) {
                Log::warning('Failed to translate product name', [
                    'product_name' => $item->product_name
                ]);
                return false;
            }

            // Search for ingredient by English name
            $ingredient = $this->searchIngredient($englishName);

            if (!$ingredient) {
                Log::warning('Ingredient not found in Spoonacular', [
                    'product_name' => $item->product_name,
                    'english_name' => $englishName
                ]);
                return false;
            }

            $ingredientId = $ingredient['id'];

            // Get detailed nutrition for 100g/100ml
            $nutritionData = $this->getIngredientInformation($ingredientId, 100, 'grams');

            if (!$nutritionData || isset($nutritionData['error'])) {
                Log::error('Failed to fetch nutrition data', [
                    'ingredient_id' => $ingredientId,
                    'error' => $nutritionData['error'] ?? 'Unknown error'
                ]);
                return false;
            }

            // Extract nutrition values
            $nutrition = $nutritionData['nutrition'] ?? [];
            $nutrients = $nutrition['nutrients'] ?? [];

            // Find specific nutrients (per 100g)
            $calories = $this->findNutrient($nutrients, 'Calories');
            $protein = $this->findNutrient($nutrients, 'Protein');
            $carbs = $this->findNutrient($nutrients, 'Carbohydrates');
            $fat = $this->findNutrient($nutrients, 'Fat');

            // Update fridge item
            $item->update([
                'spoonacular_ingredient_id' => $ingredientId,
                'calories_per_100g' => $calories,
                'protein_per_100g' => $protein,
                'carbs_per_100g' => $carbs,
                'fat_per_100g' => $fat,
                'nutrition_data' => $nutritionData, // Store full response
            ]);

            Log::info('Fridge item enriched successfully', [
                'item_id' => $item->id,
                'ingredient_id' => $ingredientId,
                'calories' => $calories
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Exception while enriching fridge item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: Find a specific nutrient value from nutrients array.
     *
     * @param array $nutrients
     * @param string $name Nutrient name (e.g., 'Calories', 'Protein')
     * @return float|null
     */
    protected function findNutrient(array $nutrients, string $name): ?float
    {
        foreach ($nutrients as $nutrient) {
            if (($nutrient['name'] ?? '') === $name) {
                return $nutrient['amount'] ?? null;
            }
        }
        return null;
    }
}
