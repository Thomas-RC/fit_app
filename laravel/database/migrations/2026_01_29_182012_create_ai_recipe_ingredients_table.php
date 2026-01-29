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
        Schema::create('ai_recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_generated_recipe_id')
                  ->constrained('ai_generated_recipes')
                  ->onDelete('cascade'); // FK do przepisu AI
            $table->foreignId('fridge_item_id')
                  ->nullable()
                  ->constrained('fridge_items')
                  ->onDelete('set null'); // FK do lodówki (nullable)
            $table->string('ingredient_name'); // Nazwa składnika po polsku
            $table->decimal('amount', 8, 2); // Ilość (np. 200.50)
            $table->string('unit', 50); // Jednostka (g, ml, sztuki, łyżka)
            $table->timestamps();

            // Indeksy
            $table->index('ai_generated_recipe_id');
            $table->index('fridge_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recipe_ingredients');
    }
};
