<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVertexAIRequest;
use App\Http\Requests\UpdateSpoonacularRequest;
use App\Models\AppSetting;
use App\Services\VertexAIService;
use App\Services\SpoonacularService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected $vertexAIService;
    protected $spoonacularService;

    public function __construct(VertexAIService $vertexAIService, SpoonacularService $spoonacularService)
    {
        $this->vertexAIService = $vertexAIService;
        $this->spoonacularService = $spoonacularService;
    }

    /**
     * Display admin settings panel.
     */
    public function settings(): View
    {
        // Get current settings - use exists() to check if key exists in database
        $vertexAIConfigured = AppSetting::where('key', 'vertex_ai_project_id')->exists();
        $spoonacularConfigured = AppSetting::where('key', 'spoonacular_api_key')->exists();

        // Get actual values using AppSetting::get() which handles decryption
        $vertexAIProjectId = AppSetting::get('vertex_ai_project_id');
        $vertexAIUpdatedAt = AppSetting::where('key', 'vertex_ai_credentials')->first()?->updated_at;

        // Mask Spoonacular API key for display
        $spoonacularKey = AppSetting::get('spoonacular_api_key');
        $maskedSpoonacularKey = $spoonacularKey ? substr($spoonacularKey, 0, 8) . '...' . substr($spoonacularKey, -4) : null;

        return view('admin.settings', compact(
            'vertexAIConfigured',
            'spoonacularConfigured',
            'vertexAIProjectId',
            'vertexAIUpdatedAt',
            'maskedSpoonacularKey'
        ));
    }

    /**
     * Update Vertex AI credentials.
     */
    public function updateVertexAI(UpdateVertexAIRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Read and validate JSON file
            $file = $request->file('credentials_file');
            $jsonContent = file_get_contents($file->getRealPath());
            $credentials = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()
                    ->route('admin.settings')
                    ->with('error', 'Nieprawidłowy plik JSON: ' . json_last_error_msg());
            }

            // Validate required fields in service account JSON
            $requiredFields = ['type', 'project_id', 'private_key', 'client_email'];
            foreach ($requiredFields as $field) {
                if (!isset($credentials[$field])) {
                    return redirect()
                        ->route('admin.settings')
                        ->with('error', "Plik JSON konta usługi nie zawiera wymaganego pola: {$field}");
                }
            }

            if ($credentials['type'] !== 'service_account') {
                return redirect()
                    ->route('admin.settings')
                    ->with('error', 'Plik JSON musi być kluczem konta usługi.');
            }

            // Save credentials (encrypted)
            AppSetting::set('vertex_ai_credentials', $jsonContent);

            // Save project ID
            AppSetting::set('vertex_ai_project_id', $validated['project_id']);

            return redirect()
                ->route('admin.settings')
                ->with('success', 'Dane uwierzytelniające Vertex AI zostały zaktualizowane!');

        } catch (\Exception $e) {
            Log::error('Admin Vertex AI Update Error: ' . $e->getMessage());
            return redirect()
                ->route('admin.settings')
                ->with('error', 'Nie udało się zaktualizować danych uwierzytelniających Vertex AI: ' . $e->getMessage());
        }
    }

    /**
     * Test Vertex AI connection.
     */
    public function testVertexAI(): JsonResponse
    {
        try {
            // Get credentials using AppSetting::get() which handles decryption
            $credentialsJson = AppSetting::get('vertex_ai_credentials');
            $projectId = AppSetting::get('vertex_ai_project_id');

            if (!$credentialsJson || !$projectId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vertex AI nie jest skonfigurowane. Najpierw prześlij dane uwierzytelniające.',
                ]);
            }

            // Try to initialize service (this would test credentials)
            // For now, we'll just check if credentials are valid JSON
            $credentials = json_decode($credentialsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nieprawidłowy format danych uwierzytelniających. Błąd: ' . json_last_error_msg(),
                ]);
            }

            // Perform actual API test by making a simple request
            $testResult = $this->vertexAIService->testConnection();

            if (isset($testResult['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Połączenie z API nie powiodło się: ' . $testResult['error'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Połączenie z Vertex AI udane! API działa poprawnie.',
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Vertex AI Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test połączenia nie powiódł się: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update Spoonacular API key.
     */
    public function updateSpoonacular(UpdateSpoonacularRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Save to app settings (encrypted)
            AppSetting::set('spoonacular_api_key', $validated['api_key']);

            return redirect()
                ->route('admin.settings')
                ->with('success', 'Klucz API Spoonacular został zaktualizowany!');

        } catch (\Exception $e) {
            Log::error('Admin Spoonacular Update Error: ' . $e->getMessage());
            return redirect()
                ->route('admin.settings')
                ->with('error', 'Nie udało się zaktualizować klucza API Spoonacular: ' . $e->getMessage());
        }
    }

    /**
     * Test Spoonacular API connection.
     */
    public function testSpoonacular(): JsonResponse
    {
        try {
            // Test with simple ingredient search (minimal quota usage)
            $result = $this->spoonacularService->searchIngredient('milk');

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test API nie powiódł się: Brak wyników',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'API Spoonacular działa poprawnie! Znaleziono: ' . ($result['name'] ?? 'ingredient'),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Spoonacular Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test połączenia nie powiódł się: ' . $e->getMessage(),
            ]);
        }
    }

}
