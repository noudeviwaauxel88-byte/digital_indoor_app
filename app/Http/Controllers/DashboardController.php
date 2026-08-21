<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Requête groupée pour optimiser les performances du tableau de bord
        $rawStats = $user->tasks()
            ->toBase() // Utilise le Query Builder de base pour éviter les colonnes pivot automatiques
            ->selectRaw("
                COUNT(CASE WHEN status = 'todo' THEN 1 END) as todo,
                COUNT(CASE WHEN status IN ('in_progress', 'to_validate') THEN 1 END) as in_progress,
                COUNT(CASE WHEN status IN ('done', 'cancelled') THEN 1 END) as done,
                COUNT(CASE WHEN due_date < NOW() AND status NOT IN ('done', 'cancelled') THEN 1 END) as overdue
            ")
            ->first();

        $stats = [
            'todo'        => $rawStats->todo ?? 0,
            'in_progress' => $rawStats->in_progress ?? 0,
            'done'        => $rawStats->done ?? 0,
            'overdue'     => $rawStats->overdue ?? 0,
        ];

        $recentTasks = $user->tasks()
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentTasks'));
    }
}