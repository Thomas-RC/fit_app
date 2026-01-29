<?php

namespace App\Services;

use App\Models\User;
use App\Models\MealPlan;
use App\Models\MealPlanRecipe;
use App\Models\AiGeneratedRecipe;
use App\Models\AiRecipeIngredient;
use App\Models\FridgeItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MealPlannerService
{
    protected $vertexAIService;

    public function __construct(VertexAIService $vertexAIService)
    {
        $this->vertexAIService = $vertexAIService;
    }

    /**
     * Generate a complete meal plan for a user on a specific date using 100% Vertex AI.
     *
     * @param User $user
     * @param string $date (Y-m-d format)
     * @return MealPlan|null
     */
    public function generateMealPlanForUser(User $user, string $date): ?MealPlan
    {
        Log::info('Starting AI-first meal plan generation', [
            'user_id' => $user->id,
            'date' => $date
        ]);

        try {
            DB::beginTransaction();

            // Get user preferences and fridge items
            $preferences = $user->preferences ?? [];
            $targetCalories = $preferences['daily_calories'] ?? 2000;
            $dietType = $preferences['diet_type'] ?? 'omnivore';

            // Get fridge items (ingredients available)
            $fridgeItems = FridgeItem::where('user_id', $user->id)
                ->get()
                ->pluck('product_name')
                ->toArray();

            Log::info('User data loaded', [
                'target_calories' => $targetCalories,
                'diet_type' => $dietType,
                'fridge_items_count' => count($fridgeItems)
            ]);

            // Create meal plan
            $mealPlan = MealPlan::create([
                'user_id' => $user->id,
                'date' => $date,
                'target_calories' => $targetCalories,
                'total_calories' => 0, // Will be updated after generating meals
            ]);

            Log::info('MealPlan created', ['meal_plan_id' => $mealPlan->id]);

            // Calculate calories per meal
            $calorieDistribution = $this->calculateCalorieDistribution($targetCalories);

            $totalCalories = 0;
            $mealTypes = ['breakfast', 'lunch', 'dinner'];
            $generatedMeals = []; // Track generated meals for variety

            // Generate each meal using Vertex AI
            foreach ($mealTypes as $mealType) {
                $targetMealCalories = $calorieDistribution[$mealType];

                Log::info("Generating {$mealType}...", [
                    'target_calories' => $targetMealCalories,
                ]);

                // Generate recipe with AI (pass previous meals for variety)
                $recipeData = $this->generateAiRecipe($mealType, $targetMealCalories, $dietType, $fridgeItems, $generatedMeals);

                if (!$recipeData) {
                    Log::error("Failed to generate {$mealType}, skipping");
                    continue;
                }

                // Save to database
                $mealPlanRecipe = $this->saveAiRecipeToDB(
                    $recipeData,
                    $mealPlan->id,
                    $mealType,
                    $user->id
                );

                if ($mealPlanRecipe) {
                    $totalCalories += $mealPlanRecipe->calories;

                    // Add to generated meals list for variety in next meals
                    $generatedMeals[] = $recipeData['title'];

                    Log::info("{$mealType} saved successfully", [
                        'recipe_id' => $mealPlanRecipe->ai_generated_recipe_id,
                        'title' => $mealPlanRecipe->recipe_title,
                        'calories' => $mealPlanRecipe->calories,
                        'running_total' => $totalCalories
                    ]);
                } else {
                    Log::error("Failed to save {$mealType} to database");
                }
            }

            // Update total calories
            $mealPlan->update(['total_calories' => $totalCalories]);

            DB::commit();

            Log::info('Meal plan generation completed', [
                'meal_plan_id' => $mealPlan->id,
                'target_calories' => $targetCalories,
                'actual_calories' => $totalCalories,
            ]);

            return $mealPlan;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Meal plan generation failed', [
                'user_id' => $user->id,
                'date' => $date,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Calculate calorie distribution across meals.
     *
     * @param int $totalCalories
     * @return array ['breakfast' => int, 'lunch' => int, 'dinner' => int]
     */
    protected function calculateCalorieDistribution(int $totalCalories): array
    {
        return [
            'breakfast' => (int) ($totalCalories * 0.30), // 30%
            'lunch' => (int) ($totalCalories * 0.40),     // 40%
            'dinner' => (int) ($totalCalories * 0.30),    // 30%
        ];
    }

    /**
     * Generate a single recipe using Vertex AI.
     *
     * @param string $mealType (breakfast/lunch/dinner)
     * @param int $targetCalories
     * @param string $dietType
     * @param array $fridgeItems
     * @param array $previousMeals Already generated meal titles for variety
     * @return array|null Recipe data with ingredients
     */
    protected function generateAiRecipe(
        string $mealType,
        int $targetCalories,
        string $dietType,
        array $fridgeItems,
        array $previousMeals = []
    ): ?array
    {
        // Map meal type to Polish
        $mealTypePolish = [
            'breakfast' => 'śniadanie',
            'lunch' => 'obiad',
            'dinner' => 'kolacja',
        ][$mealType] ?? $mealType;

        Log::info("Generating AI recipe", [
            'meal_type' => $mealType,
            'meal_type_pl' => $mealTypePolish,
            'target_calories' => $targetCalories,
            'diet_type' => $dietType,
            'fridge_items_count' => count($fridgeItems),
            'previous_meals_count' => count($previousMeals)
        ]);

        // Call Vertex AI to generate complete recipe
        return $this->vertexAIService->generateCompleteRecipe(
            $mealTypePolish,
            $targetCalories,
            $dietType,
            $fridgeItems,
            $previousMeals
        );
    }

    /**
     * Save AI-generated recipe to database.
     *
     * @param array $recipeData Data from Vertex AI
     * @param int $mealPlanId
     * @param string $mealType
     * @param int $userId For linking ingredients to fridge
     * @return MealPlanRecipe|null
     */
    protected function saveAiRecipeToDB(
        array $recipeData,
        int $mealPlanId,
        string $mealType,
        int $userId
    ): ?MealPlanRecipe
    {
        try {
            // Create AI generated recipe
            $aiRecipe = AiGeneratedRecipe::create([
                'title' => $recipeData['title'],
                'instructions' => $recipeData['instructions'],
                'servings' => $recipeData['servings'],
                'ready_in_minutes' => $recipeData['ready_in_minutes'],
                'estimated_calories' => $recipeData['estimated_calories'],
                'meal_type' => $mealType,
            ]);

            Log::info("AI recipe created", [
                'ai_recipe_id' => $aiRecipe->id,
                'title' => $aiRecipe->title
            ]);

            // Save ingredients
            $count = 0;
            foreach ($recipeData['ingredients'] as $ingredient) {
                $fridgeItemId = null;

                // Try to find matching fridge item if from_fridge=true
                if ($ingredient['from_fridge']) {
                    $fridgeItem = FridgeItem::where('user_id', $userId)
                        ->where('product_name', 'LIKE', '%' . $ingredient['name'] . '%')
                        ->first();

                    if ($fridgeItem) {
                        $fridgeItemId = $fridgeItem->id;
                    }
                }

                AiRecipeIngredient::create([
                    'ai_generated_recipe_id' => $aiRecipe->id,
                    'fridge_item_id' => $fridgeItemId,
                    'ingredient_name' => $ingredient['name'],
                    'amount' => $ingredient['amount'],
                    'unit' => $ingredient['unit'],
                ]);

                $count++;
            }

            Log::info("Saved ingredients", [
                'ai_recipe_id' => $aiRecipe->id,
                'count' => $count
            ]);

            // Link to meal plan
            $mealPlanRecipe = MealPlanRecipe::create([
                'meal_plan_id' => $mealPlanId,
                'ai_generated_recipe_id' => $aiRecipe->id,
                'spoonacular_recipe_id' => null, // No Spoonacular
                'meal_type' => $mealType,
                'recipe_title' => $aiRecipe->title,
                'calories' => $aiRecipe->estimated_calories,
                'recipe_data' => null, // Data is in ai_generated_recipes table
            ]);

            Log::info("Meal plan recipe linked", [
                'meal_plan_recipe_id' => $mealPlanRecipe->id
            ]);

            return $mealPlanRecipe;

        } catch (\Exception $e) {
            Log::error('Failed to save AI recipe to DB', [
                'error' => $e->getMessage(),
                'recipe_title' => $recipeData['title'] ?? 'unknown'
            ]);

            return null;
        }
    }

    // ========================================
    // FUTURE: Additional Helper Methods
    // ========================================

    /**
     * TODO: Validate AI recipe response
     * TODO: Handle AI generation failures
     * TODO: Add retry logic
     * TODO: Add snack generation if needed
     */
}
