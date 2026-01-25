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
        Schema::create('fridge_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('product_name');
            $table->decimal('quantity', 8, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->date('expires_at')->nullable();
            $table->timestamps();

            // Indeksy
            $table->index('user_id');
            $table->index('added_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fridge_items');
    }
};
