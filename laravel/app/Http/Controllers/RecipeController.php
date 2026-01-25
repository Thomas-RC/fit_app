<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRecipesRequest;
use App\Services\SpoonacularService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class RecipeController extends Controller
{
    protected $spoonacularService;

    public function __construct(SpoonacularService $spoonacularService)
    {
        $this->spoonacularService = $spoonacularService;
    }

    /**
     * Display a listing of recipes with filters.
     */
    public function index(SearchRecipesRequest $request): View
    {
        $validated = $request->validated();

        // Build cache key from parameters
        $cacheKey = 'recipes_' . md5(json_encode($validated));

        // Cache for 1 hour
        $result = Cache::remember($cacheKey, 3600, function () use ($validated) {
            $params = [];

            if (!empty($validated['query'])) {
                $params['query'] = $validated['query'];
            }

            if (!empty($validated['diet'])) {
                $params['diet'] = $validated['diet'];
            }

            if (!empty($validated['maxCalories'])) {
                $params['maxCalories'] = $validated['maxCalories'];
            }

            if (!empty($validated['cuisine'])) {
                $params['cuisine'] = $validated['cuisine'];
            }

            if (!empty($validated['offset'])) {
                $params['offset'] = $validated['offset'];
            }

            return $this->spoonacularService->complexSearch($params);
        });

        $recipes = $result['results'] ?? [];
        $totalResults = $result['totalResults'] ?? 0;
        $offset = $validated['offset'] ?? 0;
        $perPage = 12;

        return view('recipes.index', compact('recipes', 'totalResults', 'offset', 'perPage', 'validated'));
    }

    /**
     * Display the specified recipe.
     */
    public function show(int $recipeId): View
    {
        // Cache recipe information for 24 hours
        $cacheKey = 'recipe_' . $recipeId;

        $recipe = Cache::remember($cacheKey, 86400, function () use ($recipeId) {
            return $this->spoonacularService->getRecipeInformation($recipeId);
        });

        if (isset($recipe['error'])) {
            abort(404, 'Recipe not found');
        }

        // Get user's fridge items to check which ingredients they have
        $userIngredients = [];
        if (auth()->check()) {
            $userIngredients = auth()->user()
                ->fridgeItems()
                ->pluck('product_name')
                ->map(fn($name) => strtolower($name))
                ->toArray();
        }

        return view('recipes.show', compact('recipe', 'userIngredients'));
    }

    /**
     * Get random recipes (for inspiration).
     */
    public function random(): View
    {
        $preferences = [];

        if (auth()->check() && auth()->user()->preferences) {
            $preferences = [
                'diet_type' => auth()->user()->preferences->diet_type,
            ];
        }

        $result = $this->spoonacularService->getRandomRecipes($preferences, 12);
        $recipes = $result['recipes'] ?? [];

        return view('recipes.index', compact('recipes'));
    }
}
