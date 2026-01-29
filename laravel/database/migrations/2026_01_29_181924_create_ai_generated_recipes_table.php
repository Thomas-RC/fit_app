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
        Schema::create('ai_generated_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Nazwa przepisu po polsku
            $table->text('instructions'); // Instrukcje gotowania po polsku
            $table->unsignedInteger('servings')->default(2); // Liczba porcji
            $table->unsignedInteger('ready_in_minutes')->nullable(); // Czas przygotowania
            $table->unsignedInteger('estimated_calories'); // Szacowane kalorie (całość)
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']); // Typ posiłku
            $table->timestamps();

            // Indeksy
            $table->index('meal_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_recipes');
    }
};
