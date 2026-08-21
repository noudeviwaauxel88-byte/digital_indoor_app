<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Liste tous les projets.
     */
    public function index()
{
    $projects = Project::withCount('tasks')
        ->with('members')
        ->latest()
        ->get();

    $users = User::select('id', 'firstname', 'lastname', 'email')
        ->orderBy('firstname')
        ->get();

    return view('projects.index', compact('projects', 'users'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name'          => 'required|string|max:255',
        'structure'     => 'nullable|string|max:255',
        'start_date'    => 'nullable|date',
        'description'   => 'nullable|string',
        'visibility'    => 'required|in:private,public',
        'invited_users' => 'nullable|array',
        'invited_users.*' => 'exists:users,id',
    ]);

    $project = Project::create([
        'name'        => $validated['name'],
        'structure'   => $validated['structure'] ?? null,
        'start_date'  => $validated['start_date'] ?? null,
        'description' => $validated['description'] ?? null,
        'visibility'  => $validated['visibility'],
        'user_id'     => auth()->id(),
    ]);

    if (!empty($validated['invited_users'])) {
        $project->members()->attach($validated['invited_users']);
    }

    return redirect()->route('projects.index')
        ->with('success', 'Projet créé avec succès.');
}

    /**
     * Affiche les détails d'un projet spécifique.
     */
    public function show(Project $project)
{
    // Charger les relations nécessaires du projet
    $project->load(['members', 'tasks.user']);

    // Récupérer la liste de tous les utilisateurs pour l'attribution dans Alpine.js
    $users = \App\Models\User::select('id', 'firstname', 'lastname', 'email')
        ->orderBy('firstname')
        ->get();

    return view('projects.show', compact('project', 'users'));
}

    /**
     * Formulaire d'édition de projet.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Met à jour un projet existant.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|string|in:planning,in_progress,completed,on_hold',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'color'       => 'nullable|string|max:7',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projet mis à jour avec succès.');
    }

    /**
     * Supprime un projet.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projet supprimé avec succès.');
    }
}