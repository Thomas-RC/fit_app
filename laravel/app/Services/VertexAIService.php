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
     * Test Vertex AI connection by making a simple API call.
     *
     * @return array ['success' => true] or ['error' => 'message']
     */
    public function testConnection(): array
    {
        try {
            if (!$this->projectId) {
                return ['error' => 'Project ID nie zostało skonfigurowane'];
            }

            // Try to get access token
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return ['error' => 'Nie udało się uzyskać tokenu dostępu. Sprawdź dane uwierzytelniające konta usługi.'];
            }

            // Make a simple test request to Vertex AI API
            $prompt = "Hello";
            $response = $this->callGeminiText($prompt, 0.1);

            if (isset($response['error'])) {
                return ['error' => $response['error']];
            }

            if (empty($response['text'])) {
                return ['error' => 'API zwróciło pustą odpowiedź'];
            }

            return ['success' => true, 'message' => 'Połączenie udane'];

        } catch (\Exception $e) {
            Log::error('Vertex AI Connection Test Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
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

            // Prepare the prompt for Gemini - request Polish product names directly
            $prompt = "Przeanalizuj to zdjęcie lodówki i wypisz wszystkie widoczne produkty spożywcze. Dla każdego produktu podaj: product_name (nazwa produktu PO POLSKU), estimated_quantity (jako liczba), unit (kg, g, szt, ml, l), oraz estimated_expires_days (dni do wygaśnięcia, lub null jeśli nieznane). Zwróć odpowiedź jako tablicę JSON. WAŻNE: Nazwy produktów MUSZĄ być po polsku (np. 'mleko', 'jajka', 'ser', 'masło').";

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
            Log::error('Vertex AI not configured - PROJECT_ID is required');
            return ['error' => 'Vertex AI nie jest prawidłowo skonfigurowane. Sprawdź plik .env i upewnij się, że GOOGLE_CLOUD_PROJECT_ID jest ustawione.'];
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

            // Log translation details
            Log::info('Translation response details', [
                'input_count' => count($texts),
                'output_count' => count($translatedTexts),
                'raw_response' => substr($response['text'], 0, 500), // First 500 chars
                'first_input' => $texts[0] ?? null,
                'first_output' => $translatedTexts[0] ?? null
            ]);

            // Validate that we got the same number of translations
            if (count($translatedTexts) === count($texts)) {
                return $translatedTexts;
            }

            Log::warning('Translation count mismatch, returning original texts', [
                'expected' => count($texts),
                'received' => count($translatedTexts),
                'sample_inputs' => array_slice($texts, 0, 3),
                'sample_outputs' => array_slice($translatedTexts, 0, 3)
            ]);
            return $texts;

        } catch (\Exception $e) {
            Log::error('Translation Error: ' . $e->getMessage());
            return $texts; // Return original texts on error
        }
    }

    /**
     * Translate ingredient names from Polish to English using Vertex AI.
     * Used for Spoonacular API calls which require English ingredient names.
     *
     * @param array $polishIngredients Array of Polish ingredient names
     * @return array Translated English ingredient names in the same order
     */
    public function translateIngredientsToEnglish(array $polishIngredients): array
    {
        if (empty($polishIngredients)) {
            return [];
        }

        try {
            // Prepare texts for translation
            $textList = [];
            foreach ($polishIngredients as $index => $ingredient) {
                $textList[] = ($index + 1) . ". " . $ingredient;
            }

            // Build translation prompt
            $prompt = "Translate the following ingredient names from Polish to English. ";
            $prompt .= "Use simple, common English names that would be recognized by a recipe API. ";
            $prompt .= "Return ONLY a JSON array of translated ingredient names in the same order.\n\n";
            $prompt .= "Polish ingredients to translate:\n";
            $prompt .= implode("\n", $textList);
            $prompt .= "\n\nExamples:\n";
            $prompt .= "- mleko → milk\n";
            $prompt .= "- jajka → eggs\n";
            $prompt .= "- ser → cheese\n";
            $prompt .= "- papryka żółta → yellow bell pepper\n";
            $prompt .= "- cebula czerwona → red onion\n\n";
            $prompt .= "Return format: [\"translation 1\", \"translation 2\", ...]\n";
            $prompt .= "Return ONLY the JSON array, no other text.";

            // Call Gemini API
            $response = $this->callGeminiText($prompt);

            if (isset($response['error'])) {
                Log::warning('Ingredient translation failed, returning original texts');
                return $polishIngredients;
            }

            // Parse the response
            $translatedIngredients = $this->parseTranslationResponse($response['text']);

            // Validate that we got the same number of translations
            if (count($translatedIngredients) === count($polishIngredients)) {
                Log::info('Successfully translated ingredients to English', [
                    'count' => count($translatedIngredients)
                ]);
                return $translatedIngredients;
            }

            Log::warning('Ingredient translation count mismatch, returning original texts', [
                'expected' => count($polishIngredients),
                'received' => count($translatedIngredients)
            ]);
            return $polishIngredients;

        } catch (\Exception $e) {
            Log::error('Ingredient Translation Error: ' . $e->getMessage());
            return $polishIngredients; // Return original texts on error
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
     * @param int $limit Number of recipes to select (default: 3 for full meal plan, 1 for single meal)
     * @return array Selected recipe IDs
     */
    public function selectBestRecipes(array $recipes, array $preferences, array $recentRecipeIds = [], array $fridgeItems = [], int $limit = 3): array
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

            Log::info('AI recipe selection input', [
                'total_recipes' => count($recipeList),
                'recipes_with_calories' => count(array_filter($recipeList, fn($r) => $r['calories'] !== null && $r['calories'] > 0)),
                'target_calories' => $preferences['daily_calories'] ?? 2000,
                'calorie_range' => [
                    'min' => min(array_column($recipeList, 'calories')),
                    'max' => max(array_column($recipeList, 'calories')),
                    'avg' => round(array_sum(array_column($recipeList, 'calories')) / count($recipeList))
                ]
            ]);

            // Build the prompt
            $prompt = $this->buildRecipeSelectionPrompt($recipeList, $preferences, $recentRecipeIds, $fridgeItems, $limit);

            // Call Gemini API with higher temperature for more variety
            $response = $this->callGeminiText($prompt, 0.9);

            if (isset($response['error'])) {
                Log::warning('VertexAI selection failed, falling back to random selection');
                return $this->fallbackRecipeSelection($recipes, $recentRecipeIds, $limit);
            }

            // Parse the response
            $selectedIds = $this->parseRecipeSelectionResponse($response['text']);

            // Validate that we got valid recipe IDs
            $validIds = array_intersect($selectedIds, array_column($recipes, 'id'));

            if (count($validIds) >= $limit) {
                $finalSelection = array_slice(array_values($validIds), 0, $limit);

                // Log what AI selected
                $selectedRecipes = array_filter($recipeList, fn($r) => in_array($r['id'], $finalSelection));
                Log::info('AI recipe selection result', [
                    'limit' => $limit,
                    'selected_ids' => $finalSelection,
                    'selected_calories' => array_column($selectedRecipes, 'calories'),
                    'total_calories' => array_sum(array_column($selectedRecipes, 'calories'))
                ]);

                return $finalSelection;
            }

            // If AI didn't return enough valid IDs, fallback
            Log::warning('VertexAI returned insufficient valid recipe IDs, using fallback');
            return $this->fallbackRecipeSelection($recipes, $recentRecipeIds, $limit);

        } catch (\Exception $e) {
            Log::error('Recipe Selection Error: ' . $e->getMessage());
            return $this->fallbackRecipeSelection($recipes, $recentRecipeIds, $limit);
        }
    }

    /**
     * Build the prompt for recipe selection.
     */
    protected function buildRecipeSelectionPrompt(array $recipeList, array $preferences, array $recentRecipeIds, array $fridgeItems, int $limit): string
    {
        $dietType = $preferences['diet_type'] ?? 'omnivore';
        $targetCalories = $preferences['daily_calories'] ?? 2000;
        $allergies = !empty($preferences['allergies']) ? implode(', ', $preferences['allergies']) : 'brak';

        $prompt = "Jesteś AI dietetykiem pomagającym stworzyć zrównoważony dzienny plan posiłków.\n\n";
        $prompt .= "PROFIL UŻYTKOWNIKA:\n";
        $prompt .= "- Typ diety: {$dietType}\n";
        $prompt .= "- Docelowa dzienna kaloryczność: {$targetCalories} kcal\n";
        $prompt .= "- Alergie/Nietolerancje: {$allergies}\n";

        if (!empty($fridgeItems)) {
            $prompt .= "- Dostępne składniki: " . implode(', ', array_slice($fridgeItems, 0, 10)) . "\n";
        }

        $prompt .= "\nOSTATNIO UŻYTE PRZEPISY (unikaj ich): ";
        $prompt .= !empty($recentRecipeIds) ? implode(', ', $recentRecipeIds) : 'brak';
        $prompt .= "\n\n";

        $prompt .= "DOSTĘPNE PRZEPISY:\n";
        foreach ($recipeList as $idx => $recipe) {
            $prompt .= ($idx + 1) . ". ID: {$recipe['id']} - \"{$recipe['title']}\"\n";

            if ($recipe['calories'] !== null) {
                $prompt .= "   Kalorie: {$recipe['calories']} kcal\n";
            }

            if (!empty($recipe['usedIngredients'])) {
                $prompt .= "   Wykorzystuje: " . implode(', ', $recipe['usedIngredients']) . "\n";
            }
            if (!empty($recipe['missedIngredients'])) {
                $prompt .= "   Brakuje: " . implode(', ', $recipe['missedIngredients']) . "\n";
            }
            $prompt .= "   Popularność: {$recipe['likes']} polubień\n\n";
        }

        $prompt .= "\nZADANIE:\n";

        // Different prompts based on limit
        if ($limit === 1) {
            // Single meal selection
            $prompt .= "Wybierz dokładnie 1 NAJLEPSZY przepis z listy.\n\n";
            $prompt .= "KRYTERIA WYBORU:\n";
            $prompt .= "1. Maksymalizuj wykorzystanie dostępnych składników z lodówki\n";
            $prompt .= "2. UNIKAJ ostatnio użytych przepisów (wymienionych powyżej)\n";
            $prompt .= "3. Preferuj przepisy z wyższą popularnością\n";
            $prompt .= "4. Wybierz przepis zgodny z dietą i bez alergenów\n\n";
        } else {
            // Full meal plan (3 meals)
            $prompt .= "Wybierz dokładnie {$limit} przepisy na dzienny plan posiłków:\n";
            $prompt .= "1. ŚNIADANIE - lekkie, energetyczne, odpowiednie na poranek (np. owsianka, jajecznica, placki)\n";
            $prompt .= "2. OBIAD - główny posiłek dnia, pełnowartościowy (np. danie mięsne, zupa, makaron z dodatkami)\n";
            $prompt .= "3. KOLACJA - sycący posiłek wieczorny (np. sałatka, drugie danie, lekkie main course)\n";
            $prompt .= "⛔ ABSOLUTNIE WYKLUCZ: desery, ciasta, słodycze - to NIE są posiłki główne!\n\n";
            $prompt .= "⚠️⚠️⚠️ ULTRA-KRYTYCZNE WYMAGANIE - PRECYZYJNY BILANS KALORYCZNY ⚠️⚠️⚠️\n";
            $prompt .= "SUMA {$limit} przepisów MUSI ABSOLUTNIE wynosić między " . ($targetCalories - 20) . " a " . ($targetCalories + 20) . " kcal\n";
            $prompt .= "TOLERANCJA: MAKSYMALNIE ±20 KCAL!\n";
            $prompt .= "CEL: DOKŁADNIE {$targetCalories} kcal - nawet odchylenie o 5 kcal jest lepsze niż 10 kcal!\n\n";
            $prompt .= "ALGORYTM WYBORU (MATEMATYCZNY - NIE ZGADUJ!):\n";
            $prompt .= "1. OBLICZ wszystkie możliwe kombinacje {$limit} przepisów i ich sumy kaloryczne\n";
            $prompt .= "2. WYBIERZ kombinację z NAJMNIEJSZYM odchyleniem od {$targetCalories} kcal\n";
            $prompt .= "3. Jeśli kilka kombinacji ma podobne odchylenie (±5 kcal), wybierz tę z lepszym rozkładem:\n";
            $prompt .= "   - Śniadanie: 20-30% dziennej kaloryczności\n";
            $prompt .= "   - Obiad: 35-45% dziennej kaloryczności\n";
            $prompt .= "   - Kolacja: 30-40% dziennej kaloryczności\n\n";
            $prompt .= "DRUGORZĘDNE KRYTERIA (tylko po spełnieniu wymogu kalorycznego):\n";
            $prompt .= "2. Maksymalizuj wykorzystanie dostępnych składników\n";
            $prompt .= "3. Twórz RÓŻNORODNOŚĆ - różne metody gotowania, składniki i kuchnie\n";
            $prompt .= "4. UNIKAJ ostatnio użytych przepisów (wymienionych powyżej) - to jest ważne!\n";
            $prompt .= "5. Równoważ odżywianie w ciągu dnia (białko, węglowodany, zdrowe tłuszcze)\n";
            $prompt .= "6. Uwzględnij odpowiednie typy posiłków (lżejsze śniadanie, obfitszy obiad/kolacja)\n";
            $prompt .= "7. Preferuj przepisy z wyższą popularnością gdy inne czynniki są równe\n\n";
        }

        $prompt .= "Zwróć TYLKO tablicę JSON z {$limit} ID przepisów";
        if ($limit === 3) {
            $prompt .= " w kolejności [id_sniadanie, id_obiad, id_kolacja]";
        }
        $prompt .= ".\n";
        $prompt .= "Przykładowy format: " . ($limit === 1 ? "[123456]" : "[123456, 789012, 345678]") . "\n";
        $prompt .= "Zwróć TYLKO tablicę JSON, bez żadnego innego tekstu.";

        return $prompt;
    }

    /**
     * Call Gemini for text-only prompt.
     */
    protected function callGeminiText(string $prompt, float $temperature = 0.7): array
    {
        if (!$this->projectId) {
            Log::warning('Vertex AI not configured');
            return ['error' => 'Nie skonfigurowane'];
        }

        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                throw new \Exception('Nie udało się uzyskać tokenu dostępu');
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
                    'temperature' => $temperature,
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
                return ['error' => 'Błąd API'];
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
     * Fallback recipe selection when AI fails - tries to balance calories.
     */
    protected function fallbackRecipeSelection(array $recipes, array $recentRecipeIds, int $limit = 3): array
    {
        // Filter out recently used recipes
        $availableRecipes = array_filter($recipes, function($recipe) use ($recentRecipeIds) {
            return !in_array($recipe['id'], $recentRecipeIds);
        });

        // If we filtered out too many, use all recipes
        if (count($availableRecipes) < $limit) {
            $availableRecipes = $recipes;
        }

        // Sort by calories (descending) to prefer higher-calorie recipes
        usort($availableRecipes, function($a, $b) {
            $caloriesA = $a['calories'] ?? 0;
            $caloriesB = $b['calories'] ?? 0;
            return $caloriesB <=> $caloriesA;
        });

        // Take N recipes with highest calories to get closer to target
        $selected = array_slice($availableRecipes, 0, $limit);

        Log::warning('Using fallback recipe selection', [
            'limit' => $limit,
            'selected_ids' => array_column($selected, 'id'),
            'selected_calories' => array_column($selected, 'calories'),
            'total_calories' => array_sum(array_column($selected, 'calories'))
        ]);

        return array_column($selected, 'id');
    }
}
