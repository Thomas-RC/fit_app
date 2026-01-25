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
                ->with('error', 'You already have a meal plan for this date. Please choose a different date or delete the existing plan first.');
        }

        try {
            // Generate meal plan using service
            $result = $this->mealPlannerService->generateMealPlanForUser($user, $date);

            // Check if result is an error array (from API limit exceeded)
            if (is_array($result) && isset($result['error'])) {
                $errorMessage = $result['error'];

                // Check if it's an API limit error
                if (isset($result['api_limit']) && $result['api_limit']) {
                    $errorMessage = 'Spoonacular API daily limit has been reached. Please try again tomorrow or upgrade your API plan.';
                }

                return redirect()
                    ->route('meal-plans.create')
                    ->with('error', $errorMessage);
            }

            if (!$result) {
                return redirect()
                    ->route('meal-plans.create')
                    ->with('error', 'Failed to generate meal plan. Please try again or check your API configuration.');
            }

            return redirect()
                ->route('meal-plans.index')
                ->with('success', 'Meal plan generated successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->route('meal-plans.create')
                ->with('error', 'An error occurred while generating the meal plan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified meal plan.
     */
    public function show(MealPlan $mealPlan): View
    {
        // Policy check
        $this->authorize('view', $mealPlan);

        $mealPlan->load('recipes');

        // Calculate nutrition breakdown
        $totalProtein = 0;
        $totalCarbs = 0;
        $totalFat = 0;

        foreach ($mealPlan->recipes as $recipe) {
            $recipeData = $recipe->recipe_data;
            if (isset($recipeData['nutrition']['nutrients'])) {
                foreach ($recipeData['nutrition']['nutrients'] as $nutrient) {
                    if ($nutrient['name'] === 'Protein') {
                        $totalProtein += $nutrient['amount'];
                    } elseif ($nutrient['name'] === 'Carbohydrates') {
                        $totalCarbs += $nutrient['amount'];
                    } elseif ($nutrient['name'] === 'Fat') {
                        $totalFat += $nutrient['amount'];
                    }
                }
            }
        }

        // Get user's fridge items for comparison (in Polish, lowercase)
        $userFridgeItems = $mealPlan->user->fridgeItems()
            ->pluck('product_name')
            ->map(fn($name) => strtolower(trim($name)))
            ->toArray();

        // Collect all unique ingredients from all recipes (using Polish translations from database)
        $allIngredientsPolish = [];
        foreach ($mealPlan->recipes as $recipe) {
            $recipeData = $recipe->recipe_data;

            // Get ingredients from extendedIngredients (already translated in database)
            if (isset($recipeData['extendedIngredients']) && is_array($recipeData['extendedIngredients'])) {
                foreach ($recipeData['extendedIngredients'] as $ingredient) {
                    // Use translated Polish name from database, fallback to English if not available
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
            ->with('success', 'Meal plan deleted successfully!');
    }
}
