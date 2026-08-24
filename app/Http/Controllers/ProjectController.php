<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Liste tous les projets avec filtrage par statut et recherche.
     */
    public function index(Request $request)
    {
        $query = Project::withCount('tasks')
            ->with('members');

        // Filtrage par statut
        if ($request->filled('status')) {
            $status = $request->input('status');
            
            if ($status === 'in_progress') {
                $query->where('status', 'in_progress');
            } elseif ($status === 'completed') {
                $query->where('status', 'completed');
            } elseif ($status === 'late') {
                // Projet considéré en retard si la date de fin est dépassée et que le statut n'est pas terminé
                $query->where('status', '!=', 'completed')
                      ->whereNotNull('end_date')
                      ->where('end_date', '<', now());
            }
        }

        // Recherche par mot-clé
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('structure', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->get();

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
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'description'   => 'nullable|string',
            'visibility'    => 'required|in:private,public',
            'invited_users' => 'nullable|array',
            'invited_users.*' => 'exists:users,id',
            'document'      => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('project_documents', 'public');
        }

        $project = Project::create([
            'name'        => $validated['name'],
            'structure'   => $validated['structure'] ?? null,
            'start_date'  => $validated['start_date'] ?? null,
            'end_date'    => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'visibility'  => $validated['visibility'],
            'file_path'   => $filePath,
            'user_id'     => auth()->id(),
        ]);

        if (!empty($validated['invited_users'])) {
            $project->members()->attach($validated['invited_users']);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Projet créé avec succès.');
    }

    public function show(Project $project)
    {
        $project->load(['members', 'tasks.user']);

        $users = User::select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();

        return view('projects.show', compact('project', 'users'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|string|in:planning,in_progress,completed,on_hold',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'color'       => 'nullable|string|max:7',
            'document'    => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:10240',
        ]);

        if ($request->hasFile('document')) {
            if ($project->file_path && Storage::disk('public')->exists($project->file_path)) {
                Storage::disk('public')->delete($project->file_path);
            }
            $validated['file_path'] = $request->file('document')->store('project_documents', 'public');
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projet mis à jour avec succès.');
    }

    public function destroy(Project $project)
    {
        if ($project->file_path && Storage::disk('public')->exists($project->file_path)) {
            Storage::disk('public')->delete($project->file_path);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projet supprimé avec succès.');
    }

    /**
     * Télécharge ou affiche le document joint du projet.
     */
    public function downloadDocument(Project $project)
    {
        if (!$project->file_path || !Storage::disk('public')->exists($project->file_path)) {
            return back()->with('error', 'Le document joint est introuvable.');
        }

        return Storage::disk('public')->response($project->file_path);
    }
}