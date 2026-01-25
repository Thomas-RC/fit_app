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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('diet_type', ['omnivore', 'vegetarian', 'vegan', 'keto'])
                  ->default('omnivore');
            $table->unsignedInteger('daily_calories')->default(2000);
            $table->json('allergies')->nullable();
            $table->json('exclude_ingredients')->nullable();
            $table->timestamps();

            // Indeksy
            $table->unique('user_id');
            $table->index('diet_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
