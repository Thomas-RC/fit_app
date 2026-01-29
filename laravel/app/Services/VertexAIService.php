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
                    'maxOutputTokens' => 4096, // Increased for longer recipes
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

    // ========================================
    // MEAL RECIPE GENERATION (AI-FIRST)
    // ========================================

    /**
     * Generate complete recipe in Polish using Vertex AI.
     *
     * @param string $mealType 'śniadanie'|'obiad'|'kolacja'|'przekąska'
     * @param int $targetCalories Target calories for this meal
     * @param string $dietType 'wegetariańska'|'wegańska'|'keto'|'omnivore'
     * @param array $fridgeItems Available ingredients from user's fridge
     * @param array $previousMeals Already generated meals for variety
     * @return array|null Parsed recipe data or null on error
     */
    public function generateCompleteRecipe(
        string $mealType,
        int $targetCalories,
        string $dietType,
        array $fridgeItems,
        array $previousMeals = []
    ): ?array
    {
        try {
            $prompt = $this->buildRecipePrompt($mealType, $targetCalories, $dietType, $fridgeItems, $previousMeals);

            Log::info('Generating complete recipe with AI', [
                'meal_type' => $mealType,
                'target_calories' => $targetCalories,
                'diet_type' => $dietType,
                'fridge_items_count' => count($fridgeItems)
            ]);

            // Call Gemini API
            $response = $this->callGeminiText($prompt, 0.7);

            if (isset($response['error'])) {
                Log::error('Failed to generate recipe', [
                    'error' => $response['error'],
                    'meal_type' => $mealType
                ]);
                return null;
            }

            // Parse JSON response
            $recipe = $this->parseRecipeResponse($response['text']);

            if (!$recipe) {
                Log::error('Failed to parse recipe response', [
                    'meal_type' => $mealType,
                    'response_preview' => substr($response['text'], 0, 200)
                ]);
                return null;
            }

            // Validate recipe
            if (!$this->validateRecipe($recipe)) {
                Log::error('Recipe validation failed', [
                    'meal_type' => $mealType,
                    'recipe' => $recipe
                ]);
                return null;
            }

            Log::info('Recipe generated successfully', [
                'title' => $recipe['title'],
                'calories' => $recipe['estimated_calories'],
                'ingredients_count' => count($recipe['ingredients'])
            ]);

            return $recipe;

        } catch (\Exception $e) {
            Log::error('Exception in generateCompleteRecipe: ' . $e->getMessage(), [
                'meal_type' => $mealType,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Build prompt for recipe generation.
     */
    protected function buildRecipePrompt(
        string $mealType,
        int $targetCalories,
        string $dietType,
        array $fridgeItems,
        array $previousMeals = []
    ): string
    {
        $fridgeList = empty($fridgeItems)
            ? "Brak składników w lodówce"
            : "- " . implode("\n- ", array_slice($fridgeItems, 0, 30)); // Max 30

        $dietText = [
            'wegetariańska' => 'wegetariańska (bez mięsa i ryb)',
            'wegańska' => 'wegańska (bez produktów zwierzęcych)',
            'keto' => 'ketogeniczna (nisko-węglowodanowa)',
            'omnivore' => 'wszystkożerna (MOŻE i POWINNA zawierać mięso, ryby, drób - używaj różnorodnych źródeł białka)'
        ][$dietType] ?? 'wszystkożerna (MOŻE zawierać mięso)';

        $previousMealsText = '';
        if (!empty($previousMeals)) {
            $previousMealsText = "\nJUŻ WYGENEROWANE POSIŁKI DZISIAJ (unikaj powtórzeń!):\n- " . implode("\n- ", $previousMeals) . "\n";
        }

        $prompt = <<<PROMPT
Jesteś doświadczonym dietetykiem i kucharzem. Wygeneruj przepis na {$mealType} o kaloryczności około {$targetCalories} kcal.

SKŁADNIKI Z LODÓWKI UŻYTKOWNIKA:
{$fridgeList}

DIETA: {$dietText}
{$previousMealsText}

ZASADY:
- RÓŻNORODNOŚĆ: Generuj RÓŻNE rodzaje potraw (nie powtarzaj omletów, sałatek itp.)
- Używaj RÓŻNYCH technik gotowania (smażenie, pieczenie, gotowanie, duszenie, surówki)
- Jeśli są już wygenerowane posiłki - unikaj podobnych składników głównych i technik
- Używaj GŁÓWNIE składników z lodówki użytkownika (priorytet!)
- Możesz dodać MAKSYMALNIE 5 składników do dokupienia (bez przypraw)
- Podstawowe przyprawy (sól, pieprz, cukier) nie liczą się do limitu 5
- Podaj DOKŁADNE ilości dla każdego składnika (gramy, mililitry, sztuki, łyżki)
- Instrukcje krok po kroku w jasny i zrozumiały sposób
- Szacuj kalorie realistycznie
- Wszystko po polsku
- Czas przygotowania realistyczny (10-60 minut)

FORMAT ODPOWIEDZI (JSON):
{
  "title": "Nazwa przepisu po polsku",
  "servings": 2,
  "ready_in_minutes": 30,
  "estimated_calories": {$targetCalories},
  "ingredients": [
    {
      "name": "jajka",
      "amount": 3,
      "unit": "sztuki",
      "from_fridge": true
    },
    {
      "name": "ser feta",
      "amount": 50,
      "unit": "g",
      "from_fridge": false
    }
  ],
  "instructions": "1. Rozbij jajka do miski i ubij je widelcem.\\n2. Rozgrzej patelnię na średnim ogniu...\\n3. Smaż przez 3-4 minuty..."
}

WAŻNE:
- Zwróć TYLKO JSON, bez żadnego dodatkowego tekstu
- Tablica "ingredients" musi mieć DOKŁADNE ilości (amount) i jednostki (unit)
- Pole "from_fridge" = true jeśli składnik jest z lodówki użytkownika
- Instrukcje jako jeden ciągły string z \\n między krokami
- Maksymalnie 5 składników z from_fridge=false
PROMPT;

        return $prompt;
    }

    /**
     * Parse recipe JSON response from AI.
     */
    protected function parseRecipeResponse(string $text): ?array
    {
        // Clean response - remove markdown code blocks
        $text = trim($text);

        // Remove ```json at start and ``` at end
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        // Try to extract JSON if there's text before/after
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $text = $matches[0];
        }

        try {
            $recipe = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON parse error: ' . json_last_error_msg(), [
                    'response_length' => strlen($text),
                    'response_preview' => substr($text, 0, 300),
                    'response_end' => substr($text, -100)
                ]);
                return null;
            }

            return $recipe;

        } catch (\Exception $e) {
            Log::error('Exception parsing recipe: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate recipe structure.
     */
    protected function validateRecipe(array $recipe): bool
    {
        // Required fields
        $required = ['title', 'servings', 'estimated_calories', 'ingredients', 'instructions'];

        foreach ($required as $field) {
            if (!isset($recipe[$field])) {
                Log::warning("Missing required field: {$field}");
                return false;
            }
        }

        // Validate ingredients
        if (!is_array($recipe['ingredients']) || empty($recipe['ingredients'])) {
            Log::warning("Invalid or empty ingredients array");
            return false;
        }

        // Count ingredients to buy (max 5, excluding basic seasonings: sól, pieprz, cukier)
        $basicSeasonings = ['sól', 'pieprz', 'cukier'];
        $toBuy = array_filter($recipe['ingredients'], function($i) use ($basicSeasonings) {
            if ($i['from_fridge'] ?? true) {
                return false; // From fridge, skip
            }

            // Exclude basic seasonings from count
            $name = strtolower($i['name'] ?? '');
            foreach ($basicSeasonings as $seasoning) {
                if (str_contains($name, $seasoning)) {
                    return false; // Basic seasoning, skip
                }
            }

            return true; // Count this ingredient
        });

        if (count($toBuy) > 5) {
            Log::warning("Too many ingredients to buy (excluding seasonings)", [
                'count' => count($toBuy),
                'ingredients' => array_column($toBuy, 'name')
            ]);
            return false;
        }

        // Validate each ingredient
        foreach ($recipe['ingredients'] as $ingredient) {
            if (!isset($ingredient['name']) || !isset($ingredient['amount']) || !isset($ingredient['unit'])) {
                Log::warning("Invalid ingredient structure", ['ingredient' => $ingredient]);
                return false;
            }
        }

        return true;
    }
}
