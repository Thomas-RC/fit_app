<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiGeneratedRecipe extends Model
{
    protected $fillable = [
        'title',
        'instructions',
        'servings',
        'ready_in_minutes',
        'estimated_calories',
        'meal_type',
    ];

    protected $casts = [
        'servings' => 'integer',
        'ready_in_minutes' => 'integer',
        'estimated_calories' => 'integer',
    ];

    /**
     * Składniki przepisu
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(AiRecipeIngredient::class);
    }

    /**
     * Powiązania z planami posiłków
     */
    public function mealPlanRecipes(): HasMany
    {
        return $this->hasMany(MealPlanRecipe::class);
    }
}
