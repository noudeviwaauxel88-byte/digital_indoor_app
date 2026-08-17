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
    Schema::table('tasks', function (Blueprint $table) {
        $table->string('structure')->nullable()->after('description');
        $table->string('module')->nullable()->after('structure');
        $table->string('document_path')->nullable()->after('module'); // Pour stocker le chemin du fichier
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
    }
};
