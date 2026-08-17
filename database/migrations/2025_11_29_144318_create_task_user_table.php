<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Optionnel : Supprimer l'ancienne colonne user_id de la table tasks si vous voulez nettoyer
        // Schema::table('tasks', function (Blueprint $table) {
        //     $table->dropForeign(['user_id']);
        //     $table->dropColumn('user_id');
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
    }
};