<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs avec leurs rôles (optimisée avec pagination et sélection de colonnes).
     */
    public function index()
    {
        // Seul le SuperAdmin peut voir cette page[cite: 1, 12]
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        // Requête optimisée avec sélection des colonnes existantes, eager loading et pagination
        $users = User::select('id', 'firstname', 'lastname', 'job_title', 'email', 'created_at')
            ->with('roles:id,name')
            ->latest()
            ->paginate(15);

        // Sélection optimisée pour les rôles[cite: 12]
        $roles = Role::select('id', 'name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Mettre à jour le rôle d'un utilisateur.
     */
    public function updateRole(Request $request, User $user)
    {
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        // On enlève les anciens rôles et on met le nouveau[cite: 1, 12]
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur.
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        // Empêcher de se supprimer soi-même[cite: 1, 12]
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }
}