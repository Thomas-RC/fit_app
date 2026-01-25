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
        Schema::create('meal_plan_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_id')
                  ->constrained('meal_plans')
                  ->onDelete('cascade');
            $table->unsignedInteger('spoonacular_recipe_id');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->string('recipe_title');
            $table->unsignedInteger('calories');
            $table->json('recipe_data')->nullable();
            $table->timestamps();

            // Indeksy
            $table->index('meal_plan_id');
            $table->index('spoonacular_recipe_id');
            $table->index('meal_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_plan_recipes');
    }
};
