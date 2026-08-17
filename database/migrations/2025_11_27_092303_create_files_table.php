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
    Schema::create('files', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('path');
        $table->string('type')->nullable(); // pdf, jpg, docx...
        $table->integer('size')->nullable(); // en octets
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
