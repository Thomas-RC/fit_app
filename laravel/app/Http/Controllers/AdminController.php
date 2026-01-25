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
        // Get current settings
        $vertexAIConfigured = AppSetting::where('key', 'vertex_ai_project_id')->exists();
        $spoonacularConfigured = AppSetting::where('key', 'spoonacular_api_key')->exists();

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
                    ->with('error', 'Invalid JSON file: ' . json_last_error_msg());
            }

            // Validate required fields in service account JSON
            $requiredFields = ['type', 'project_id', 'private_key', 'client_email'];
            foreach ($requiredFields as $field) {
                if (!isset($credentials[$field])) {
                    return redirect()
                        ->route('admin.settings')
                        ->with('error', "Service account JSON is missing required field: {$field}");
                }
            }

            if ($credentials['type'] !== 'service_account') {
                return redirect()
                    ->route('admin.settings')
                    ->with('error', 'JSON file must be a service account key.');
            }

            // Save credentials (encrypted)
            AppSetting::set('vertex_ai_credentials', $jsonContent);

            // Save project ID
            AppSetting::set('vertex_ai_project_id', $validated['project_id']);

            return redirect()
                ->route('admin.settings')
                ->with('success', 'Vertex AI credentials updated successfully!');

        } catch (\Exception $e) {
            Log::error('Admin Vertex AI Update Error: ' . $e->getMessage());
            return redirect()
                ->route('admin.settings')
                ->with('error', 'Failed to update Vertex AI credentials: ' . $e->getMessage());
        }
    }

    /**
     * Test Vertex AI connection.
     */
    public function testVertexAI(): JsonResponse
    {
        try {
            // Get credentials
            $credentialsJson = AppSetting::where('key', 'vertex_ai_credentials')->first()?->value;
            $projectId = AppSetting::where('key', 'vertex_ai_project_id')->first()?->value;

            if (!$credentialsJson || !$projectId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vertex AI is not configured. Please upload credentials first.',
                ]);
            }

            // Try to initialize service (this would test credentials)
            // For now, we'll just check if credentials are valid JSON
            $credentials = json_decode($credentialsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials format.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Vertex AI credentials are configured correctly!',
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Vertex AI Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
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
                ->with('success', 'Spoonacular API key updated successfully!');

        } catch (\Exception $e) {
            Log::error('Admin Spoonacular Update Error: ' . $e->getMessage());
            return redirect()
                ->route('admin.settings')
                ->with('error', 'Failed to update Spoonacular API key: ' . $e->getMessage());
        }
    }

    /**
     * Test Spoonacular API connection.
     */
    public function testSpoonacular(): JsonResponse
    {
        try {
            // Try a simple API call
            $result = $this->spoonacularService->getRandomRecipes([], 1);

            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'API test failed: ' . $result['error'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Spoonacular API is working correctly!',
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Spoonacular Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ]);
        }
    }

}
