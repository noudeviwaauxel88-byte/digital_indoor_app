<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'description', // Nous allons réutiliser `title` pour la description principale
        'status',
        'priority',
        'start_date',
        'due_date',
        'structure', // <-- AJOUTÉ
        'module',    // <-- AJOUTÉ
        'document_path', // <-- AJOUTÉ
    ];

    /**
     * La tâche appartient à un projet.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * La tâche est assignée à un utilisateur.
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user');
    }
}
