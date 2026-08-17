<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Clé étrangère vers la table projects (peut être nulle si on choisit "Autre")
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            
            // Champ texte pour une autre destination
            $table->string('other_destination')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'other_destination']);
        });
    }
};