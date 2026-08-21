<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show(Project $project)
    {
        $project->load(['tasks.assignedUser', 'members']);

        // Sélection uniquement des colonnes requises pour optimiser la mémoire
        $allUsers = User::select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('projects.show', compact('project', 'allUsers'));
    }
}