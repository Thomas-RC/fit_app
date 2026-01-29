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
        Schema::table('meal_plan_recipes', function (Blueprint $table) {
            // Make spoonacular_recipe_id nullable (was required before)
            $table->unsignedInteger('spoonacular_recipe_id')->nullable()->change();

            // Add new column for AI-generated recipes
            $table->foreignId('ai_generated_recipe_id')
                  ->nullable()
                  ->after('spoonacular_recipe_id')
                  ->constrained('ai_generated_recipes')
                  ->onDelete('cascade');

            // Add index
            $table->index('ai_generated_recipe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meal_plan_recipes', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['ai_generated_recipe_id']);
            $table->dropIndex(['ai_generated_recipe_id']);
            $table->dropColumn('ai_generated_recipe_id');

            // Revert spoonacular_recipe_id to not nullable (original state)
            $table->unsignedInteger('spoonacular_recipe_id')->nullable(false)->change();
        });
    }
};
