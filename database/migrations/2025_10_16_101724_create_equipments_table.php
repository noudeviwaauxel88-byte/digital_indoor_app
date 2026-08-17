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
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->decimal('price', 10, 2);
            // $table->integer('quantity'); // <-- SUPPRIMÉ
            $table->date('entry_date')->nullable(); 
            $table->string('brand')->nullable();
            $table->text('features')->nullable();
            $table->string('image_path')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};