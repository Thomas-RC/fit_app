<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDish extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'ingredients',
        'instructions',
        'calories',
        'protein',
        'carbs',
        'fat',
        'image_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ingredients' => 'array',
            'protein' => 'decimal:2',
            'carbs' => 'decimal:2',
            'fat' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the custom dish.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
