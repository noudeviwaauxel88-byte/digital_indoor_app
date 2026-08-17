<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Créateur
            
            // Infos de base
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            // Liaisons Projet / Autre
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->string('other_destination')->nullable();
            
            // Type d'activité
            $table->string('activity_type'); // 'reunion', 'formation', 'mission', 'autre'
            $table->string('color')->default('#3788d8'); // Couleur dans le calendrier
            
            // Champs dynamiques (nullables)
            // Pour Réunion & Formation
            $table->json('participants_ids')->nullable(); // Liste des IDs utilisateurs invités
            
            // Pour Formation
            $table->string('trainer')->nullable();
            $table->text('modules')->nullable();
            
            // Pour Mission
            $table->string('intervenant')->nullable();
            $table->string('institution')->nullable();
            $table->string('location')->nullable();
            
            // Pour Autre (détails texte)
            $table->text('other_details')->nullable();
            
            // Fichier joint
            $table->string('file_path')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};