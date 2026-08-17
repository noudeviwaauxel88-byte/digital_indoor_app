<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Event;
use App\Models\Project;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Statistiques des Tâches (Via la relation Many-to-Many 'tasks')
        // On compte les tâches assignées à l'utilisateur connecté en groupant les statuts
        
        $stats = [
            // À FAIRE : Uniquement 'Non commencé'
            'todo' => $user->tasks()->where('status', 'todo')->count(),
            
            // EN COURS : 'En cours' + 'À valider'
            'in_progress' => $user->tasks()->whereIn('status', ['in_progress', 'to_validate'])->count(),
            
            // TERMINÉES : 'Achevée' + 'Annulée'
            'done' => $user->tasks()->whereIn('status', ['done', 'cancelled'])->count(),
            
            // EN RETARD : Date dépassée et pas terminé/annulé
            'overdue' => $user->tasks()
                            ->where('due_date', '<', now())
                            ->whereNotIn('status', ['done', 'cancelled'])
                            ->count(),
        ];

        // 2. Événements à venir (Les 3 prochains)
        $upcomingEvents = Event::where('start_date', '>=', Carbon::today())
                                ->orderBy('start_date', 'asc')
                                ->take(3)
                                ->get();

        // 3. Projets Récents (Créateur ou Membre)
        $recentProjects = Project::where('user_id', $user->id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->withCount('tasks')
            ->latest('updated_at')
            ->take(5)
            ->get();

        // 4. Mes Tâches Assignées (Liste à afficher)
        // On affiche tout sauf ce qui est terminé ou annulé (donc on inclut 'to_validate')
        $myTasks = $user->tasks()
                        ->whereNotIn('status', ['done', 'cancelled'])
                        ->with('project')
                        ->orderBy('due_date', 'asc') // Les plus urgentes en premier
                        ->get()
                        ->groupBy(function($task) {
                            return $task->project ? $task->project->name : 'Sans Projet';
                        });

        return view('dashboard', compact('stats', 'upcomingEvents', 'recentProjects', 'myTasks'));
    }
}