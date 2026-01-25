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
}
