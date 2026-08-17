<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index()
    {
        // SÉCURITÉ : Seul le SuperAdmin peut voir les logs
        if (!Auth::user()->hasRole('SuperAdmin')) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        // Récupérer les 100 dernières activités
        $activities = Activity::with('causer') // "Causer" = Celui qui a fait l'action
            ->latest()
            ->paginate(20);

        return view('activity_logs.index', compact('activities'));
    }
}