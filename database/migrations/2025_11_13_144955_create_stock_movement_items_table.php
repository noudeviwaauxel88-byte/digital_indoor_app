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
        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('stock_movement_id')->constrained()->onDelete('cascade'); // <-- LIGNE BOGUÉE
            // $table->foreignId('equipment_item_id')->constrained()->onDelete('cascade'); // <-- LIGNE BOGUÉE
            
            $table->unsignedBigInteger('stock_movement_id'); // <-- Remplacement
            $table->unsignedBigInteger('equipment_item_id'); // <-- Remplacement
            
            $table->timestamps();

            // --- Définition explicite des clés étrangères ---
            $table->foreign('stock_movement_id')
                  ->references('id')
                  ->on('stock_movements')
                  ->onDelete('cascade');
            
            $table->foreign('equipment_item_id')
                  ->references('id')
                  ->on('equipment_items')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
    }
};