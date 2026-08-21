<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord principal.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Statistiques des tâches de l'utilisateur
        $rawStats = $user->tasks()
            ->toBase()
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

        // 2. Tâches assignées à l'utilisateur (groupées par projet)
        $myTasks = $user->tasks()
            ->with('project')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('due_date', 'asc')
            ->get()
            ->groupBy(function ($task) {
                return $task->project ? $task->project->name : 'Sans projet';
            });

        // 3. Événements à venir (Correction pour la variable $upcomingEvents)
        $upcomingEvents = Event::where('start_date', '>=', now()->startOfDay())
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

        // 4. Projets récents pour l'accès rapide
        $recentProjects = Project::withCount('tasks')
            ->latest()
            ->take(4)
            ->get();

        // 5. Envoi de toutes les variables à la vue
        return view('dashboard', compact(
            'stats',
            'myTasks',
            'upcomingEvents',
            'recentProjects'
        ));
    }
}