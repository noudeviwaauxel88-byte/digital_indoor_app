<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Enregistre une nouvelle tâche.
     * SÉCURITÉ : Interdit aux Employés[cite: 8].
     */
    public function store(Request $request, Project $project)
    {
        // 1. SÉCURITÉ : Vérification du rôle[cite: 8]
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action refusée. Vous n\'avez pas les droits pour créer une tâche.');
        }

        $validatedData = $request->validate([
            'title'       => 'required|string|max:255',
            'status'      => 'required|string|max:50',
            'priority'    => 'required|string|max:50',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date|after_or_equal:start_date',
            'structure'   => 'nullable|string|max:255',
            'module'      => 'nullable|string|max:255',
            'document'    => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:5000',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        if ($request->hasFile('document')) {
            $validatedData['document_path'] = $request->file('document')->store('task_documents', 'public');
        }

        $validatedData['project_id'] = $project->id;
        $task = Task::create($validatedData);

        // Sauvegarder les relations (Sync)[cite: 8]
        if (!empty($validatedData['assignees'])) {
            $task->assignees()->sync($validatedData['assignees']);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Tâche créée avec succès !');
    }

    /**
     * Met à jour une tâche existante.
     * SÉCURITÉ : Interdit aux Employés[cite: 8].
     */
    public function update(Request $request, Task $task)
    {
        // 1. SÉCURITÉ[cite: 8]
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action refusée. Contactez votre manager pour modifier cette tâche.');
        }

        $validatedData = $request->validate([
            'title'       => 'required|string|max:255',
            'status'      => 'required|string|max:50',
            'priority'    => 'required|string|max:50',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date|after_or_equal:start_date',
            'structure'   => 'nullable|string|max:255',
            'module'      => 'nullable|string|max:255',
            'document'    => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:5000',
            'assignees'   => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        // Gestion du fichier en update (remplacement)[cite: 8]
        if ($request->hasFile('document')) {
            if ($task->document_path && Storage::disk('public')->exists($task->document_path)) {
                Storage::disk('public')->delete($task->document_path);
            }
            $validatedData['document_path'] = $request->file('document')->store('task_documents', 'public');
        }

        $task->update($validatedData);

        // Mise à jour des relations[cite: 8]
        if ($request->has('assignees')) {
            $task->assignees()->sync($validatedData['assignees'] ?? []);
        }

        return redirect()->back()->with('success', 'Tâche mise à jour.');
    }

    /**
     * Supprime une tâche.
     * SÉCURITÉ : Interdit aux Employés[cite: 8].
     */
    public function destroy(Task $task)
    {
        // 1. SÉCURITÉ[cite: 8]
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action refusée. Vous ne pouvez pas supprimer de tâches.');
        }

        $project = $task->project;
        
        if ($task->document_path && Storage::disk('public')->exists($task->document_path)) {
            Storage::disk('public')->delete($task->document_path);
        }

        $task->delete();

        return redirect()->route('projects.show', $project)->with('success', 'Tâche supprimée avec succès.');
    }
}