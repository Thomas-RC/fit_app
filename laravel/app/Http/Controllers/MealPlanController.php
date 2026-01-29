<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateMealPlanRequest;
use App\Models\MealPlan;
use App\Services\MealPlannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MealPlanController extends Controller
{
    protected $mealPlannerService;

    public function __construct(MealPlannerService $mealPlannerService)
    {
        $this->mealPlannerService = $mealPlannerService;
    }

    /**
     * Display a listing of the user's meal plans.
     */
    public function index(): View
    {
        $user = auth()->user();

        $mealPlans = $user->mealPlans()
            ->with('recipes')
            ->latest('date')
            ->paginate(9);

        return view('meal-plans.index', compact('mealPlans'));
    }

    /**
     * Show the form for generating a new meal plan (wizard).
     */
    public function create(): View
    {
        $user = auth()->user();

        $fridgeItemsCount = $user->fridgeItems()->count();
        $preferences = $user->preferences;

        return view('meal-plans.generate', compact('fridgeItemsCount', 'preferences'));
    }

    /**
     * Generate a new meal plan.
     */
    public function generate(GenerateMealPlanRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $date = $request->validated()['date'];

        // Check if meal plan for this date already exists
        $existingPlan = $user->mealPlans()->where('date', $date)->first();

        if ($existingPlan) {
            return redirect()
                ->route('meal-plans.create')
                ->with('error', 'Posiadasz już plan posiłków na tę datę. Wybierz inną datę lub najpierw usuń istniejący plan.');
        }

        try {
            // Generate meal plan using service
            $result = $this->mealPlannerService->generateMealPlanForUser($user, $date);

            // Check if result is an error array (from API limit exceeded)
            if (is_array($result) && isset($result['error'])) {
                $errorMessage = $result['error'];

                // Check if it's an API limit error
                if (isset($result['api_limit']) && $result['api_limit']) {
                    $errorMessage = 'Został osiągnięty dzienny limit API Spoonacular. Spróbuj ponownie jutro lub ulepsz swój plan API.';
                }

                return redirect()
                    ->route('meal-plans.create')
                    ->with('error', $errorMessage);
            }

            if (!$result) {
                return redirect()
                    ->route('meal-plans.create')
                    ->with('error', 'Nie udało się wygenerować planu posiłków. Spróbuj ponownie lub sprawdź konfigurację API.');
            }

            return redirect()
                ->route('meal-plans.index')
                ->with('success', 'Plan posiłków został wygenerowany!');

        } catch (\Exception $e) {
            return redirect()
                ->route('meal-plans.create')
                ->with('error', 'Wystąpił błąd podczas generowania planu posiłków: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified meal plan.
     */
    public function show(MealPlan $mealPlan): View
    {
        // Policy check
        $this->authorize('view', $mealPlan);

        // Load recipes with AI-generated data and fridge items
        $mealPlan->load('recipes.aiGeneratedRecipe.ingredients.fridgeItem');

        // Calculate nutrition breakdown from fridge items
        $totalProtein = 0;
        $totalCarbs = 0;
        $totalFat = 0;

        // Calculate macros from ingredients with nutrition data
        foreach ($mealPlan->recipes as $recipe) {
            if ($recipe->isAiGenerated() && $recipe->aiGeneratedRecipe) {
                foreach ($recipe->aiGeneratedRecipe->ingredients as $ingredient) {
                    // Only calculate if ingredient has nutrition data from fridge
                    if ($ingredient->fridgeItem && $ingredient->fridgeItem->calories_per_100g) {
                        $fridgeItem = $ingredient->fridgeItem;

                        // Convert amount to grams (handles different units)
                        $amountInGrams = $this->convertToGrams(
                            $ingredient->amount,
                            $ingredient->unit,
                            $ingredient->ingredient_name
                        );

                        // Calculate nutrition for this ingredient (per 100g basis)
                        $factor = $amountInGrams / 100; // Convert to 100g units

                        $totalProtein += ($fridgeItem->protein_per_100g ?? 0) * $factor;
                        $totalCarbs += ($fridgeItem->carbs_per_100g ?? 0) * $factor;
                        $totalFat += ($fridgeItem->fat_per_100g ?? 0) * $factor;
                    }
                }
            }
        }

        // Get user's fridge items for comparison (in Polish, lowercase)
        $userFridgeItems = $mealPlan->user->fridgeItems()
            ->pluck('product_name')
            ->map(fn($name) => strtolower(trim($name)))
            ->toArray();

        // Collect all unique ingredients from AI-generated recipes
        $allIngredientsPolish = [];
        foreach ($mealPlan->recipes as $recipe) {
            // Check if this is an AI-generated recipe
            if ($recipe->isAiGenerated() && $recipe->aiGeneratedRecipe) {
                foreach ($recipe->aiGeneratedRecipe->ingredients as $ingredient) {
                    $polishName = $ingredient->ingredient_name;
                    if ($polishName) {
                        $polishNameLower = strtolower(trim($polishName));
                        if (!in_array($polishNameLower, $allIngredientsPolish)) {
                            $allIngredientsPolish[] = $polishNameLower;
                        }
                    }
                }
            } else {
                // Fallback for old Spoonacular recipes (if any exist)
                $recipeData = $recipe->recipe_data;
                if (isset($recipeData['extendedIngredients']) && is_array($recipeData['extendedIngredients'])) {
                    foreach ($recipeData['extendedIngredients'] as $ingredient) {
                        $polishName = $ingredient['name_pl'] ?? $ingredient['name'] ?? $ingredient['original'] ?? null;
                        if ($polishName) {
                            $polishNameLower = strtolower(trim($polishName));
                            if (!in_array($polishNameLower, $allIngredientsPolish)) {
                                $allIngredientsPolish[] = $polishNameLower;
                            }
                        }
                    }
                }
            }
        }

        // Filter out ingredients that user already has in fridge
        $missingIngredients = [];
        foreach ($allIngredientsPolish as $polishName) {
            // Check if ingredient is in fridge (partial match for flexibility)
            $found = false;
            foreach ($userFridgeItems as $fridgeItem) {
                // Check for partial match (e.g., "banan" matches "banany")
                if (str_contains($fridgeItem, $polishName) || str_contains($polishName, $fridgeItem)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $missingIngredients[] = ucfirst($polishName);
            }
        }

        return view('meal-plans.show', compact('mealPlan', 'totalProtein', 'totalCarbs', 'totalFat', 'missingIngredients'));
    }

    /**
     * Remove the specified meal plan.
     */
    public function destroy(MealPlan $mealPlan): RedirectResponse
    {
        $this->authorize('delete', $mealPlan);

        $mealPlan->delete();

        return redirect()
            ->route('meal-plans.index')
            ->with('success', 'Plan posiłków został usunięty!');
    }

    /**
     * Convert ingredient amount to grams for nutrition calculation.
     * Handles various units like "sztuka", "ząbek", "łyżka", etc.
     *
     * @param float $amount Original amount from recipe
     * @param string $unit Unit of measurement
     * @param string $ingredientName Ingredient name for context
     * @return float Amount in grams
     */
    protected function convertToGrams(float $amount, string $unit, string $ingredientName): float
    {
        $unit = strtolower(trim($unit));
        $ingredientLower = strtolower($ingredientName);

        // Already in grams or ml
        if (in_array($unit, ['g', 'gram', 'gramy', 'ml', 'mililitr'])) {
            return $amount;
        }

        // Kilograms to grams
        if (in_array($unit, ['kg', 'kilogram'])) {
            return $amount * 1000;
        }

        // Liters to ml (≈ grams for water-based liquids)
        if (in_array($unit, ['l', 'litr', 'litra'])) {
            return $amount * 1000;
        }

        // Pieces/items - estimate based on ingredient type
        if (in_array($unit, ['sztuka', 'sztuki', 'piece', 'pieces'])) {
            // Fruits
            if (str_contains($ingredientLower, 'banan')) return $amount * 120;
            if (str_contains($ingredientLower, 'jabłko')) return $amount * 180;
            if (str_contains($ingredientLower, 'śliwka')) return $amount * 35;
            if (str_contains($ingredientLower, 'persymon')) return $amount * 168;
            if (str_contains($ingredientLower, 'pomarańcz')) return $amount * 140;
            if (str_contains($ingredientLower, 'cytryn')) return $amount * 58;

            // Vegetables
            if (str_contains($ingredientLower, 'papryka')) return $amount * 120;
            if (str_contains($ingredientLower, 'cebul')) return $amount * 150;
            if (str_contains($ingredientLower, 'pomidor')) return $amount * 120;
            if (str_contains($ingredientLower, 'bakłażan')) return $amount * 300;

            // Eggs
            if (str_contains($ingredientLower, 'jaj')) return $amount * 50;

            // Default for unknown items
            return $amount * 100;
        }

        // Cloves (garlic)
        if (in_array($unit, ['ząbek', 'ząbki', 'clove', 'cloves'])) {
            return $amount * 3; // 1 clove ≈ 3g
        }

        // Tablespoons
        if (in_array($unit, ['łyżka', 'łyżki', 'tablespoon', 'tbsp'])) {
            // Oil/liquid
            if (str_contains($ingredientLower, 'olej') || str_contains($ingredientLower, 'oliw')) {
                return $amount * 14; // 1 tbsp oil ≈ 14g
            }
            // Solid
            return $amount * 15; // Default 15g
        }

        // Teaspoons
        if (in_array($unit, ['łyżeczka', 'łyżeczki', 'teaspoon', 'tsp'])) {
            return $amount * 5; // 1 tsp ≈ 5g
        }

        // Pinch
        if (in_array($unit, ['szczypta', 'szczypt', 'pinch'])) {
            return $amount * 0.5; // Pinch ≈ 0.5g
        }

        // Packs - varies greatly, use conservative estimate
        if (in_array($unit, ['opakowanie', 'opakowania', 'pack', 'packs'])) {
            return $amount * 200; // Average pack ≈ 200g
        }

        // Unknown unit - return as-is and log warning
        \Log::warning('Unknown unit in nutrition calculation', [
            'unit' => $unit,
            'ingredient' => $ingredientName,
            'amount' => $amount
        ]);

        return $amount;
    }
}
