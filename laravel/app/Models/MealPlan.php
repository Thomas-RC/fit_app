<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'total_calories',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the user that owns the meal plan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipes for the meal plan.
     */
    public function recipes()
    {
        return $this->hasMany(MealPlanRecipe::class);
    }
}
