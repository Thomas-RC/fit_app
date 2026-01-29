<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlanRecipe extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'meal_plan_id',
        'spoonacular_recipe_id',
        'ai_generated_recipe_id',  // Added for AI recipes
        'meal_type',
        'recipe_title',
        'calories',
        'recipe_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipe_data' => 'array',
        ];
    }

    /**
     * Get the meal plan that owns the recipe.
     */
    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class);
    }

    /**
     * Get the AI-generated recipe (if this is an AI recipe).
     */
    public function aiGeneratedRecipe()
    {
        return $this->belongsTo(AiGeneratedRecipe::class);
    }

    /**
     * Check if this is an AI-generated recipe.
     */
    public function isAiGenerated(): bool
    {
        return !is_null($this->ai_generated_recipe_id);
    }
}
