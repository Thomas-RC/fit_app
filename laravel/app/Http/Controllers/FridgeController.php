<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFridgeItemRequest;
use App\Http\Requests\UpdateFridgeItemRequest;
use App\Http\Requests\UploadFridgePhotoRequest;
use App\Models\FridgeItem;
use App\Services\VertexAIService;
use App\Services\SpoonacularService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class FridgeController extends Controller
{
    protected $vertexAIService;
    protected $spoonacularService;

    public function __construct(VertexAIService $vertexAIService, SpoonacularService $spoonacularService)
    {
        $this->vertexAIService = $vertexAIService;
        $this->spoonacularService = $spoonacularService;
    }

    /**
     * Display a listing of the user's fridge items.
     */
    public function index(): View
    {
        $user = auth()->user();

        $items = $user->fridgeItems()
            ->latest('added_at')
            ->get();

        // Calculate stats
        $totalItems = $items->count();
        $expiringSoon = $items->filter->isExpiringSoon()->count();
        $fresh = $items->filter->isFresh()->count();
        $expired = $items->filter->isExpired()->count();
        $withNutrition = $items->filter(fn($item) => !is_null($item->calories_per_100g))->count();

        return view('fridge.index', compact('items', 'totalItems', 'expiringSoon', 'fresh', 'expired', 'withNutrition'));
    }

    /**
     * Show the form for creating a new fridge item.
     */
    public function create(): View
    {
        return view('fridge.create');
    }

    /**
     * Store a newly created fridge item.
     */
    public function store(StoreFridgeItemRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $item = $user->fridgeItems()->create([
            ...$request->validated(),
            'added_at' => now(),
        ]);

        // Automatically enrich with nutrition data from Spoonacular
        $this->spoonacularService->enrichFridgeItemWithNutrition($item);

        return redirect()
            ->route('fridge.index')
            ->with('success', 'Produkt został dodany do lodówki!');
    }

    /**
     * Show the form for editing the specified fridge item.
     */
    public function edit(FridgeItem $fridgeItem): View
    {
        // Policy check is automatic via route model binding
        $this->authorize('update', $fridgeItem);

        return view('fridge.edit', compact('fridgeItem'));
    }

    /**
     * Update the specified fridge item.
     */
    public function update(UpdateFridgeItemRequest $request, FridgeItem $fridgeItem): RedirectResponse
    {
        $this->authorize('update', $fridgeItem);

        $fridgeItem->update($request->validated());

        return redirect()
            ->route('fridge.index')
            ->with('success', 'Produkt został zaktualizowany!');
    }

    /**
     * Remove the specified fridge item.
     */
    public function destroy(FridgeItem $fridgeItem): RedirectResponse
    {
        $this->authorize('delete', $fridgeItem);

        $fridgeItem->delete();

        return redirect()
            ->route('fridge.index')
            ->with('success', 'Produkt został usunięty!');
    }

    /**
     * Show the AI scan upload form.
     */
    public function scan(): View
    {
        return view('fridge.scan');
    }

    /**
     * Upload and analyze a fridge photo using AI.
     */
    public function uploadPhoto(UploadFridgePhotoRequest $request): JsonResponse
    {
        try {
            $file = $request->file('photo');

            // Generate unique filename
            $filename = 'fridge_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store temporarily using Storage facade with explicit disk
            $path = Storage::disk('local')->putFileAs('temp', $file, $filename);
            $fullPath = Storage::disk('local')->path($path);

            // Verify file exists
            if (!file_exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to save uploaded file'
                ], 500);
            }

            // Analyze with Vertex AI
            $result = $this->vertexAIService->analyzeFridgeImage($fullPath);

            // Delete temporary file
            Storage::disk('local')->delete($path);

            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'products' => $result['products']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store multiple fridge items at once (from AI scan).
     */
    public function storeBatch(Request $request): RedirectResponse
    {
        $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_name' => ['required', 'string', 'max:255'],
            'products.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'products.*.unit' => ['nullable', 'string', 'max:50'],
            'products.*.expires_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = auth()->user();
        $products = $request->products;

        // Products are already in Polish from AI detection - no translation needed
        $productsAdded = 0;

        foreach ($products as $productData) {
            $expiresAt = null;
            if (isset($productData['expires_days']) && $productData['expires_days'] > 0) {
                $expiresAt = now()->addDays($productData['expires_days']);
            }

            $item = $user->fridgeItems()->create([
                'product_name' => $productData['product_name'],
                'quantity' => $productData['quantity'] ?? null,
                'unit' => $productData['unit'] ?? null,
                'added_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            // Automatically enrich with nutrition data
            $this->spoonacularService->enrichFridgeItemWithNutrition($item);

            $productsAdded++;
        }

        return redirect()
            ->route('fridge.index')
            ->with('success', "Dodano {$productsAdded} produktów do lodówki!");
    }

    /**
     * Delete all fridge items for the authenticated user.
     */
    public function deleteAll(): RedirectResponse
    {
        $user = auth()->user();
        $deletedCount = $user->fridgeItems()->count();
        $user->fridgeItems()->delete();

        return redirect()
            ->route('fridge.index')
            ->with('success', "Usunięto wszystkie {$deletedCount} produkty z lodówki!");
    }

    /**
     * Delete selected fridge items.
     */
    public function deleteSelected(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['required', 'integer', 'exists:fridge_items,id'],
        ]);

        $user = auth()->user();

        // Delete only items that belong to the authenticated user
        $deletedCount = $user->fridgeItems()
            ->whereIn('id', $request->selected_items)
            ->delete();

        return redirect()
            ->route('fridge.index')
            ->with('success', "Usunięto {$deletedCount} zaznaczonych produktów z lodówki!");
    }
}
