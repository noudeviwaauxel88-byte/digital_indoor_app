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
        Schema::create('equipment_items', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('equipment_id')->constrained()->onDelete('cascade'); // <-- LIGNE BOGUÉE
            
            $table->unsignedBigInteger('equipment_id'); // <-- Remplacement
            
            $table->string('serial_number');
            $table->string('status')->default('en_stock'); // 'en_stock', 'sorti'
            $table->timestamps();
            
            // Un numéro de série doit être unique pour un type d'équipement
            $table->unique(['equipment_id', 'serial_number']);

            // --- Définition explicite de la clé étrangère ---
            $table->foreign('equipment_id')
                  ->references('id')
                  ->on('equipments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_items');
    }
};