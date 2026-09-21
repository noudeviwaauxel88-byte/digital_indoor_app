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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // La personne assignée
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo'); // todo, in_progress, to_validate, done, cancelled
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('structure')->nullable(); // Structure rattachée
            $table->string('module')->nullable();    // Module concerné
            $table->string('document_path')->nullable(); // Chemin du document joint
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        // Table pivot pour l'assignation de plusieurs utilisateurs si nécessaire
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('tasks');
    }
};