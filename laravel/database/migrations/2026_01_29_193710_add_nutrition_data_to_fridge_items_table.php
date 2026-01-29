<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fridge_items', function (Blueprint $table) {
            // Spoonacular ingredient ID for nutrition lookup
            $table->unsignedInteger('spoonacular_ingredient_id')->nullable()->after('product_name');

            // Basic nutrition data per 100g/100ml
            $table->decimal('calories_per_100g', 8, 2)->nullable();
            $table->decimal('protein_per_100g', 8, 2)->nullable();
            $table->decimal('carbs_per_100g', 8, 2)->nullable();
            $table->decimal('fat_per_100g', 8, 2)->nullable();

            // Full nutrition data from Spoonacular (JSON)
            $table->json('nutrition_data')->nullable();

            // Index for lookups
            $table->index('spoonacular_ingredient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fridge_items', function (Blueprint $table) {
            // Drop all nutrition columns
            $table->dropIndex(['spoonacular_ingredient_id']);
            $table->dropColumn([
                'spoonacular_ingredient_id',
                'calories_per_100g',
                'protein_per_100g',
                'carbs_per_100g',
                'fat_per_100g',
                'nutrition_data',
            ]);
        });
    }
};
