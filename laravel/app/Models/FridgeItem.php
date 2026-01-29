<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FridgeItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_name',
        'quantity',
        'unit',
        'added_at',
        'expires_at',
        // Nutrition data from Spoonacular
        'spoonacular_ingredient_id',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'nutrition_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
            'expires_at' => 'date',
            'quantity' => 'decimal:2',
            // Nutrition data casts
            'calories_per_100g' => 'decimal:2',
            'protein_per_100g' => 'decimal:2',
            'carbs_per_100g' => 'decimal:2',
            'fat_per_100g' => 'decimal:2',
            'nutrition_data' => 'array',
        ];
    }

    /**
     * Get the user that owns the fridge item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the item is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the item is expiring soon.
     */
    public function isExpiringSoon(int $days = 3): bool
    {
        return $this->expires_at
            && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= $days;
    }

    /**
     * Check if the item is fresh.
     */
    public function isFresh(): bool
    {
        return !$this->expires_at || (!$this->isExpired() && !$this->isExpiringSoon());
    }
}
