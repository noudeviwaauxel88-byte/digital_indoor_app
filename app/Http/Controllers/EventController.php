<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        // On charge les projets pour la recherche et les users pour les invitations
        $projects = Project::orderBy('name')->select('id', 'name')->get();
        // On trie par prénom et on récupère prénom + nom
$users = User::orderBy('firstname')->select('id', 'firstname', 'lastname', 'email')->get();
        
        // On récupère les événements formatés pour FullCalendar
        $events = Event::all()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->format('Y-m-d') . ($event->start_time ? 'T' . $event->start_time : ''),
                'end' => $event->end_date->format('Y-m-d') . ($event->end_time ? 'T' . $event->end_time : ''),
                'backgroundColor' => $event->color,
                'borderColor' => $event->color,
                'extendedProps' => $event->load('project'), // Charge toutes les infos pour la modale
            ];
        });

        return view('calendar.index', compact('projects', 'users', 'events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'activity_type' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            // Validation conditionnelle simple (on accepte les champs nullables)
            'participants_ids' => 'nullable|array',
            'trainer' => 'nullable|string',
            'modules' => 'nullable|string',
            'intervenant' => 'nullable|string',
            'institution' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        // Définir la couleur selon le type
        $colors = [
            'reunion' => '#3B82F6',   // Bleu
            'formation' => '#10B981', // Vert
            'mission' => '#F59E0B',   // Orange
            'autre' => '#6B7280',     // Gris
        ];

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['color'] = $colors[$request->activity_type] ?? '#3788d8';

        if ($request->hasFile('document')) {
            $data['file_path'] = $request->file('document')->store('event_documents', 'public');
        }

        Event::create($data);

        return redirect()->route('calendar.index')->with('success', 'Événement créé avec succès');
    }

    public function destroy(Event $event)
    {
        if ($event->file_path) {
            Storage::disk('public')->delete($event->file_path);
        }
        $event->delete();
        return redirect()->route('calendar.index')->with('success', 'Événement supprimé');
    }


    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'activity_type' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'participants_ids' => 'nullable|array',
            'trainer' => 'nullable|string',
            'modules' => 'nullable|string',
            'intervenant' => 'nullable|string',
            'institution' => 'nullable|string',
            'location' => 'nullable|string',
            'other_details' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // Couleurs
        $colors = [
            'reunion' => '#3B82F6',
            'formation' => '#10B981',
            'mission' => '#F59E0B',
            'autre' => '#6B7280',
        ];

        $data = $request->all();
        $data['color'] = $colors[$request->activity_type] ?? '#3788d8';

        // Gestion du fichier
        if ($request->hasFile('document')) {
            // Supprimer l'ancien si existe
            if ($event->file_path) {
                Storage::disk('public')->delete($event->file_path);
            }
            $data['file_path'] = $request->file('document')->store('event_documents', 'public');
        }

        $event->update($data);

        return redirect()->route('calendar.index')->with('success', 'Événement modifié avec succès');
    }

}