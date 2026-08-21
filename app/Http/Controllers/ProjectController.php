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

        return view('projects.index', compact('projects'));
    }

    /**
     * Enregistre un nouveau projet.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|string|in:planning,in_progress,completed,on_hold',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'color'       => 'nullable|string|max:7',
        ]);

        $project = Project::create($validated);

        // Attribuer le créateur comme membre du projet si nécessaire
        if (auth()->check()) {
            $project->members()->attach(auth()->id());
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projet créé avec succès.');
    }

    /**
     * Affiche les détails d'un projet spécifique.
     */
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