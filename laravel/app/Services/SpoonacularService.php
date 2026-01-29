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
    // FUTURE: Nutrition Data Methods
    // ========================================

    /**
     * TODO: Add methods to fetch nutrition data for ingredients
     * This will be used to enrich fridge items with calories, macros, etc.
     */
}
