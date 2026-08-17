<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Affiche la liste des projets.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('SuperAdmin')) {
            $projects = Project::with(['members', 'tasks'])->latest()->get();
        } else {
            $projects = Project::with(['members', 'tasks'])
                ->where('user_id', $user->id)
                ->orWhereHas('members', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orWhere('visibility', 'public')
                ->latest()
                ->get();
        }

        $users = User::where('id', '!=', $user->id)
                     ->orderBy('firstname')
                     ->select('id', 'firstname', 'lastname', 'email')
                     ->get();

        return view('projects.index', [
            'projects' => $projects,
            'users' => $users
        ]);
    }

    /**
     * Enregistre un nouveau projet.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action non autorisée. Seuls les Managers peuvent créer un projet.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'structure' => 'nullable|string',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'color' => 'nullable|string',
            'visibility' => 'required|in:public,private',
            'invited_users' => 'nullable|array',
            'invited_users.*' => 'exists:users,id'
        ]);
        
        $project = new Project($validatedData);
        $project->user_id = Auth::id();
        $project->save();

        $project->members()->attach(Auth::id());

        if ($request->filled('invited_users')) {
            $project->members()->attach($request->invited_users);
        }

        return redirect()->route('projects.index')->with('success', 'Projet créé avec succès !');
    }

    /**
     * Affiche un projet spécifique.
     */
    public function show(Project $project)
    {
        $user = Auth::user();
        
        // Vérification des droits d'accès
        if (!$user->hasRole('SuperAdmin')) {
            if ($project->visibility !== 'public' && $project->user_id !== $user->id && !$project->members->contains($user->id)) {
                abort(403, 'Vous n\'avez pas accès à ce projet privé.');
            }
        }

        // On charge les relations du projet
        $project->load(['members', 'tasks.assignees']);

        // MODIFICATION ICI : On récupère TOUS les utilisateurs de la base de données
        // pour pouvoir les assigner aux tâches, même s'ils ne sont pas "membres" du projet.
        $allUsers = User::orderBy('firstname')->orderBy('lastname')->get();
        
        return view('projects.show', [
            'project' => $project,
            'users' => $allUsers // On passe la variable $users à la vue
        ]);
    }

    public function edit(Project $project)
    {
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403);
        }
        return view('projects.edit', ['project' => $project]);
    }

    public function update(Request $request, Project $project)
    {
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'structure' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        $project->update($validatedData);
        return redirect()->route('projects.index')->with('success', 'Projet modifié avec succès !');
    }

    public function destroy(Project $project)
    {
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action non autorisée.');
        }

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Projet supprimé avec succès.');
    }
}