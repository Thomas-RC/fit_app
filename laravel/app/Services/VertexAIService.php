<?php

namespace App\Services;

use App\Models\AppSetting;
use Google\Cloud\AIPlatform\V1\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\PredictRequest;
use Google\Protobuf\Value;
use Google\Protobuf\Struct;
use Google\Protobuf\ListValue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VertexAIService
{
    protected $client;
    protected $projectId;
    protected $location = 'us-central1';
    protected $model = 'gemini-2.0-flash';

    public function __construct()
    {
        $this->projectId = AppSetting::get('vertex_ai_project_id');
        $credentials = AppSetting::get('vertex_ai_credentials');

        if ($credentials) {
            // Initialize Vertex AI client with credentials
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/vertex-credentials.json'));

            // Save credentials to temporary file
            file_put_contents(
                storage_path('app/vertex-credentials.json'),
                json_encode(json_decode($credentials))
            );
        }
    }

    /**
     * Analyze a fridge image using Vertex AI Gemini Vision.
     *
     * @param string $imagePath
     * @return array
     */
    public function analyzeFridgeImage(string $imagePath): array
    {
        try {
            // Read and encode image
            $imageData = base64_encode(file_get_contents($imagePath));

            // Prepare the prompt for Gemini
            $prompt = "Analyze this fridge image and list all visible food products. For each product, provide: product_name, estimated_quantity (as a number), unit (kg, g, szt, ml, l), and estimated_expires_days (days until expiration, or null if unknown). Return the response as a JSON array.";

            // Call Vertex AI API (simplified - actual implementation would use the SDK)
            // This is a placeholder for the actual API call structure

            $response = $this->callGeminiVision($prompt, $imageData);

            return $response;
        } catch (\Exception $e) {
            Log::error('Vertex AI Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Call Gemini Vision model via Vertex AI REST API.
     */
    protected function callGeminiVision(string $prompt, string $imageData): array
    {
        if (!$this->projectId) {
            Log::warning('Vertex AI not configured, returning mock data');
            // Return mock data if not configured
            return [
                'products' => [
                    ['product_name' => 'Milk', 'quantity' => 1, 'unit' => 'L', 'expires_days' => 5],
                    ['product_name' => 'Eggs', 'quantity' => 6, 'unit' => 'pieces', 'expires_days' => 10],
                    ['product_name' => 'Cheese', 'quantity' => 200, 'unit' => 'g', 'expires_days' => 14],
                ]
            ];
        }

        try {
            // Get access token for Google Cloud
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                throw new \Exception('Failed to obtain access token');
            }

            // Vertex AI Gemini endpoint (regional endpoint required)
            $endpoint = "https://{$this->location}-aiplatform.googleapis.com/v1/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";

            // Prepare request body
            $requestBody = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $imageData
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'topK' => 32,
                    'topP' => 1,
                    'maxOutputTokens' => 2048,
                ]
            ];

            // Make API call
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post($endpoint, $requestBody);

            if (!$response->successful()) {
                Log::error('Vertex AI API Error: ' . $response->body());
                throw new \Exception('Vertex AI API returned error: ' . $response->status());
            }

            $result = $response->json();

            // Extract text response from Gemini
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Parse JSON from response
            $products = $this->parseProductsFromResponse($text);

            return ['products' => $products];

        } catch (\Exception $e) {
            Log::error('Vertex AI API call failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Google Cloud access token.
     */
    protected function getAccessToken(): ?string
    {
        try {
            $credentialsPath = storage_path('app/vertex-credentials.json');

            if (!file_exists($credentialsPath)) {
                return null;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            // Create JWT
            $now = time();
            $jwt = [
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ];

            // Sign JWT
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $payload = json_encode($jwt);

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

            $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;

            openssl_sign($signatureInput, $signature, $credentials['private_key'], 'SHA256');

            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

            $jwtToken = $signatureInput . '.' . $base64UrlSignature;

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwtToken
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to get access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse products from Gemini response text.
     */
    protected function parseProductsFromResponse(string $text): array
    {
        // Try to extract JSON from response
        // Gemini might return markdown code blocks
        $text = trim($text);

        // Remove markdown code blocks if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        $text = trim($text);

        try {
            $data = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                // If the response is directly an array of products
                if (isset($data[0]) && is_array($data[0])) {
                    return $data;
                }
                // If the response has a 'products' key
                if (isset($data['products']) && is_array($data['products'])) {
                    return $data['products'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to parse JSON response: ' . $e->getMessage());
        }

        // If parsing fails, return empty array
        return [];
    }

    /**
     * Translate texts from English to Polish using Vertex AI.
     *
     * @param array $texts Array of English texts to translate
     * @return array Translated texts in the same order
     */
    public function translateToPolish(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        try {
            // Prepare texts for translation
            $textList = [];
            foreach ($texts as $index => $text) {
                $textList[] = ($index + 1) . ". " . $text;
            }

            // Build translation prompt
            $prompt = "Translate the following food/recipe names from English to Polish. ";
            $prompt .= "Keep the translations natural and appropriate for food context. ";
            $prompt .= "Return ONLY a JSON array of translated texts in the same order.\n\n";
            $prompt .= "Texts to translate:\n";
            $prompt .= implode("\n", $textList);
            $prompt .= "\n\nReturn format: [\"tłumaczenie 1\", \"tłumaczenie 2\", ...]\n";
            $prompt .= "Return ONLY the JSON array, no other text.";

            // Call Gemini API
            $response = $this->callGeminiText($prompt);

            if (isset($response['error'])) {
                Log::warning('Translation failed, returning original texts');
                return $texts;
            }

            // Parse the response
            $translatedTexts = $this->parseTranslationResponse($response['text']);

            // Validate that we got the same number of translations
            if (count($translatedTexts) === count($texts)) {
                return $translatedTexts;
            }

            Log::warning('Translation count mismatch, returning original texts');
            return $texts;

        } catch (\Exception $e) {
            Log::error('Translation Error: ' . $e->getMessage());
            return $texts; // Return original texts on error
        }
    }

    /**
     * Parse translation response from Gemini.
     */
    protected function parseTranslationResponse(string $text): array
    {
        $text = trim($text);
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        $text = trim($text);

        try {
            $data = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return array_values($data);
            }
        } catch (\Exception $e) {
            Log::error('Failed to parse translation response: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Select best recipes from Spoonacular results using AI.
     *
     * @param array $recipes Array of recipes from Spoonacular
     * @param array $preferences User preferences (diet_type, allergies, daily_calories)
     * @param array $recentRecipeIds Recently used recipe IDs to avoid repetition
     * @param array $fridgeItems Available fridge items
     * @return array Selected recipe IDs
     */
    public function selectBestRecipes(array $recipes, array $preferences, array $recentRecipeIds = [], array $fridgeItems = []): array
    {
        try {
            if (empty($recipes)) {
                return [];
            }

            // Prepare recipe data for AI (simplified to avoid token limits)
            $recipeList = [];
            foreach ($recipes as $recipe) {
                $recipeList[] = [
                    'id' => $recipe['id'],
                    'title' => $recipe['title'],
                    'usedIngredients' => array_slice(array_column($recipe['usedIngredients'] ?? [], 'name'), 0, 5),
                    'missedIngredients' => array_slice(array_column($recipe['missedIngredients'] ?? [], 'name'), 0, 3),
                    'likes' => $recipe['likes'] ?? 0,
                    'calories' => $recipe['calories'] ?? null,
                ];
            }

            // Build the prompt
            $prompt = $this->buildRecipeSelectionPrompt($recipeList, $preferences, $recentRecipeIds, $fridgeItems);

            // Call Gemini API
            $response = $this->callGeminiText($prompt);

            if (isset($response['error'])) {
                Log::warning('VertexAI selection failed, falling back to random selection');
                return $this->fallbackRecipeSelection($recipes, $recentRecipeIds);
            }

            // Parse the response
            $selectedIds = $this->parseRecipeSelectionResponse($response['text']);

            // Validate that we got valid recipe IDs
            $validIds = array_intersect($selectedIds, array_column($recipes, 'id'));

            if (count($validIds) >= 3) {
                return array_slice(array_values($validIds), 0, 3);
            }

            // If AI didn't return enough valid IDs, fallback
            Log::warning('VertexAI returned insufficient valid recipe IDs, using fallback');
            return $this->fallbackRecipeSelection($recipes, $recentRecipeIds);

        } catch (\Exception $e) {
            Log::error('Recipe Selection Error: ' . $e->getMessage());
            return $this->fallbackRecipeSelection($recipes, $recentRecipeIds);
        }
    }

    /**
     * Build the prompt for recipe selection.
     */
    protected function buildRecipeSelectionPrompt(array $recipeList, array $preferences, array $recentRecipeIds, array $fridgeItems): string
    {
        $dietType = $preferences['diet_type'] ?? 'omnivore';
        $targetCalories = $preferences['daily_calories'] ?? 2000;
        $allergies = !empty($preferences['allergies']) ? implode(', ', $preferences['allergies']) : 'none';

        $prompt = "You are a nutritionist AI helping to create a balanced daily meal plan.\n\n";
        $prompt .= "USER PROFILE:\n";
        $prompt .= "- Diet type: {$dietType}\n";
        $prompt .= "- Target daily calories: {$targetCalories} kcal\n";
        $prompt .= "- Allergies/Intolerances: {$allergies}\n";

        if (!empty($fridgeItems)) {
            $prompt .= "- Available ingredients: " . implode(', ', array_slice($fridgeItems, 0, 10)) . "\n";
        }

        $prompt .= "\nRECENTLY USED RECIPES (avoid these): ";
        $prompt .= !empty($recentRecipeIds) ? implode(', ', $recentRecipeIds) : 'none';
        $prompt .= "\n\n";

        $prompt .= "AVAILABLE RECIPES:\n";
        foreach ($recipeList as $idx => $recipe) {
            $prompt .= ($idx + 1) . ". ID: {$recipe['id']} - \"{$recipe['title']}\"\n";

            if ($recipe['calories'] !== null) {
                $prompt .= "   Calories: {$recipe['calories']} kcal\n";
            }

            if (!empty($recipe['usedIngredients'])) {
                $prompt .= "   Uses: " . implode(', ', $recipe['usedIngredients']) . "\n";
            }
            if (!empty($recipe['missedIngredients'])) {
                $prompt .= "   Missing: " . implode(', ', $recipe['missedIngredients']) . "\n";
            }
            $prompt .= "   Popularity: {$recipe['likes']} likes\n\n";
        }

        $prompt .= "\nTASK:\n";
        $prompt .= "Select exactly 3 recipes for a daily meal plan (breakfast, lunch, dinner).\n\n";
        $prompt .= "CRITERIA (in order of importance):\n";
        $prompt .= "1. CALORIE BALANCE: The 3 recipes together should total close to {$targetCalories} kcal (±200 kcal acceptable)\n";
        $prompt .= "   - Breakfast: 25-30% of daily calories (~" . round($targetCalories * 0.275) . " kcal)\n";
        $prompt .= "   - Lunch: 35-40% of daily calories (~" . round($targetCalories * 0.375) . " kcal)\n";
        $prompt .= "   - Dinner: 30-35% of daily calories (~" . round($targetCalories * 0.325) . " kcal)\n";
        $prompt .= "2. Maximize use of available ingredients\n";
        $prompt .= "3. Create variety - different cooking methods, ingredients, and cuisines\n";
        $prompt .= "4. Avoid recently used recipes (listed above)\n";
        $prompt .= "5. Balance nutrition across the day (protein, carbs, healthy fats)\n";
        $prompt .= "6. Consider appropriate meal types (lighter breakfast, substantial lunch/dinner)\n";
        $prompt .= "7. Prefer recipes with higher popularity when other factors are equal\n\n";

        $prompt .= "Return ONLY a JSON array of 3 recipe IDs in order [breakfast_id, lunch_id, dinner_id].\n";
        $prompt .= "Example format: [123456, 789012, 345678]\n";
        $prompt .= "Return ONLY the JSON array, no other text.";

        return $prompt;
    }

    /**
     * Call Gemini for text-only prompt.
     */
    protected function callGeminiText(string $prompt): array
    {
        if (!$this->projectId) {
            Log::warning('Vertex AI not configured');
            return ['error' => 'Not configured'];
        }

        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                throw new \Exception('Failed to obtain access token');
            }

            $endpoint = "https://{$this->location}-aiplatform.googleapis.com/v1/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";

            $requestBody = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ];

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post($endpoint, $requestBody);

            if (!$response->successful()) {
                Log::error('Vertex AI API Error: ' . $response->body());
                return ['error' => 'API error'];
            }

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return ['text' => $text];

        } catch (\Exception $e) {
            Log::error('Vertex AI call failed: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Parse recipe IDs from AI response.
     */
    protected function parseRecipeSelectionResponse(string $text): array
    {
        // Clean the response
        $text = trim($text);
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        $text = trim($text);

        try {
            $data = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                // Filter to only numeric IDs
                return array_filter($data, function($id) {
                    return is_numeric($id);
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to parse recipe selection: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Fallback recipe selection when AI fails.
     */
    protected function fallbackRecipeSelection(array $recipes, array $recentRecipeIds): array
    {
        // Filter out recently used recipes
        $availableRecipes = array_filter($recipes, function($recipe) use ($recentRecipeIds) {
            return !in_array($recipe['id'], $recentRecipeIds);
        });

        // If we filtered out too many, use all recipes
        if (count($availableRecipes) < 3) {
            $availableRecipes = $recipes;
        }

        // Shuffle and take first 3
        $availableRecipes = array_values($availableRecipes);
        shuffle($availableRecipes);

        return array_slice(array_column($availableRecipes, 'id'), 0, 3);
    }
}
