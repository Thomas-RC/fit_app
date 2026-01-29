<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecipeIngredient extends Model
{
    protected $fillable = [
        'ai_generated_recipe_id',
        'fridge_item_id',
        'ingredient_name',
        'amount',
        'unit',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Przepis AI do którego należy składnik
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(AiGeneratedRecipe::class, 'ai_generated_recipe_id');
    }

    /**
     * Składnik z lodówki (nullable - może być do dokupienia)
     */
    public function fridgeItem(): BelongsTo
    {
        return $this->belongsTo(FridgeItem::class);
    }

    /**
     * Czy składnik jest z lodówki użytkownika?
     */
    public function isFromFridge(): bool
    {
        return !is_null($this->fridge_item_id);
    }
}
