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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            
            // $table->unsignedBigInteger('equipment_id'); // <-- SUPPRIMÉ
            $table->unsignedBigInteger('user_id')->nullable(); 
            // $table->integer('quantity_taken'); // <-- SUPPRIMÉ

            $table->text('reason')->nullable();
            $table->date('movement_date');
            
            $table->timestamps(); 

            // $table->foreign('equipment_id')... // <-- SUPPRIMÉ

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users') 
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};