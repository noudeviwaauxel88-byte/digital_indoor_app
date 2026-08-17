<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // Seul le SuperAdmin peut voir cette page
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        $users = User::with('roles')->latest()->get();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function updateRole(Request $request, User $user)
    {
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        // On enlève les anciens rôles et on met le nouveau
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Rôle mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403);
        }

        // Empêcher de se supprimer soi-même
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }
}