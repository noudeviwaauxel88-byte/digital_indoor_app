<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Indispensable pour vérifier le rôle
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Enregistre une nouvelle tâche.
     * SÉCURITÉ : Interdit aux Employés.
     */
    public function store(Request $request, Project $project)
    {
        // 1. SÉCURITÉ : On vérifie le rôle AVANT de faire quoi que ce soit
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action refusée. Vous n\'avez pas les droits pour créer une tâche.');
        }

        $validatedData = $request->validate([
            'title' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'structure' => 'nullable|string',
            'module' => 'nullable|string',
            'document' => 'nullable|file|max:2048',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        if ($request->hasFile('document')) {
            $validatedData['document_path'] = $request->file('document')->store('task_documents', 'public');
        }

        $task = new Task($validatedData);
        $task->project_id = $project->id;
        $task->save();

        // Sauvegarder les relations (Sync)
        if (!empty($request->assignees)) {
            $task->assignees()->sync($request->assignees);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Tâche créée avec succès !');
    }

    /**
     * Met à jour une tâche existante.
     * SÉCURITÉ : Interdit aux Employés.
     */
    public function update(Request $request, Task $task)
    {
        // 1. SÉCURITÉ
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action refusée. Contactez votre manager pour modifier cette tâche.');
        }

        $validatedData = $request->validate([
            'title' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'structure' => 'nullable|string',
            'module' => 'nullable|string',
            'document' => 'nullable|file|max:2048',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        // Gestion du fichier en update (remplacement)
        if ($request->hasFile('document')) {
            // Supprimer l'ancien fichier s'il existe
            if ($task->document_path) {
                Storage::disk('public')->delete($task->document_path);
            }
            $validatedData['document_path'] = $request->file('document')->store('task_documents', 'public');
        }

        $task->update($validatedData);

        // Mise à jour des relations
        if (isset($request->assignees)) {
            $task->assignees()->sync($request->assignees);
        }

        return redirect()->back()->with('success', 'Tâche mise à jour.');
    }

    /**
     * Supprime une tâche.
     * SÉCURITÉ : Interdit aux Employés.
     */
    public function destroy(Task $task)
    {
        // 1. SÉCURITÉ
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action refusée. Vous ne pouvez pas supprimer de tâches.');
        }

        $project = $task->project;
        
        // Supprimer le document associé du stockage s'il existe
        if ($task->document_path) {
            Storage::disk('public')->delete($task->document_path);
        }

        $task->delete();

        return redirect()->route('projects.show', $project)->with('success', 'Tâche supprimée avec succès.');
    }
}