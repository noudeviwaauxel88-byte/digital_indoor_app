<x-app-layout>
    {{-- Import Chart.js pour le graphique --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="py-8">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- HEADER : Bienvenue --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">
                    Bienvenue {{ Auth::user()->firstname ?? Auth::user()->name }} !
                </h1>
                <p class="text-gray-500 mt-1">Voici un aperçu de votre activité aujourd'hui.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                {{-- COLONNE GAUCHE (Largeur 3/4) --}}
                <div class="lg:col-span-3 space-y-8">
                    
                    {{-- 1. CARTES STATISTIQUES --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                            <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">À FAIRE</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $stats['todo'] }}</div>
                            <div class="mt-2"><span class="inline-block w-2 h-2 rounded-full bg-gray-300"></span></div>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                            <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">EN COURS</div>
                            <div class="text-2xl font-bold text-[#4b49ac]">{{ $stats['in_progress'] }}</div>
                            <div class="mt-2"><span class="inline-block w-2 h-2 rounded-full bg-[#4b49ac] animate-pulse"></span></div>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                            <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">TERMINÉES</div>
                            <div class="text-2xl font-bold text-green-500">{{ $stats['done'] }}</div>
                            <div class="mt-2"><span class="inline-block w-2 h-2 rounded-full bg-green-500"></span></div>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                            <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">EN RETARD</div>
                            <div class="text-2xl font-bold text-red-500">{{ $stats['overdue'] }}</div>
                            <div class="mt-2"><span class="inline-block w-2 h-2 rounded-full bg-red-500"></span></div>
                        </div>
                    </div>

                    {{-- 2. SECTION CENTRALE (Graphique & Mes Tâches) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        {{-- GRAPHIQUE --}}
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center relative min-h-[350px]">
                            <h3 class="text-sm font-bold text-gray-400 uppercase absolute top-4 left-4">Vue d'ensemble</h3>
                            <div class="w-56 h-56 relative">
                                <canvas id="taskChart"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-4xl font-bold text-gray-800">{{ $stats['todo'] + $stats['in_progress'] }}</span>
                                    <span class="text-xs text-gray-500 uppercase font-semibold mt-1">Tâches actives</span>
                                </div>
                            </div>
                        </div>

                        {{-- MES TÂCHES ASSIGNÉES (NOUVELLE SECTION) --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-0 h-full overflow-hidden flex flex-col min-h-[350px]">
                            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-[#4b49ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                    Mes Tâches
                                </h3>
                                <span class="text-xs font-medium bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">{{ $stats['in_progress'] + $stats['todo'] }} en cours</span>
                            </div>

                            <div class="p-4 overflow-y-auto flex-1 custom-scrollbar" style="max-height: 300px;">
                                @if(isset($myTasks) && count($myTasks) > 0)
                                    <div class="space-y-6">
                                        @foreach($myTasks as $projectName => $tasks)
                                            <div>
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $projectName }}</h4>
                                                </div>
                                                <div class="space-y-2">
                                                    @foreach($tasks as $task)
                                                        <a href="{{ route('projects.show', $task->project_id) }}" class="block group">
                                                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200 hover:border-[#4b49ac] hover:shadow-sm transition cursor-pointer">
                                                                <div class="flex items-center gap-3 overflow-hidden">
                                                                    <!-- Priorité -->
                                                                    <div class="flex-shrink-0" title="Priorité: {{ $task->priority }}">
                                                                        @if($task->priority == 'urgent') <span class="w-2 h-2 rounded-full bg-red-500 block shadow-sm"></span>
                                                                        @elseif($task->priority == 'high') <span class="w-2 h-2 rounded-full bg-orange-400 block shadow-sm"></span>
                                                                        @else <span class="w-2 h-2 rounded-full bg-blue-300 block shadow-sm"></span> @endif
                                                                    </div>
                                                                    
                                                                    <div class="min-w-0">
                                                                        <p class="text-sm font-medium text-gray-800 truncate group-hover:text-[#4b49ac] transition-colors">{{ $task->title }}</p>
                                                                        <p class="text-[10px] text-gray-400 flex items-center gap-1">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                            {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M') : 'Aucune date' }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Statut -->
                                                                @if($task->status == 'in_progress')
                                                                    <div class="w-5 h-5 rounded-full border-2 border-[#4b49ac] border-t-transparent animate-spin" title="En cours"></div>
                                                                @else
                                                                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 border-dashed" title="À faire"></div>
                                                                @endif
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-full flex flex-col items-center justify-center text-center py-8 text-gray-400">
                                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-600">Tout est en ordre !</p>
                                        <p class="text-xs text-gray-400 mt-1">Aucune tâche assignée pour le moment.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 3. ÉVÉNEMENTS À VENIR --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Évènements à venir</h3>
                            <a href="{{ route('calendar.index') }}" class="text-sm text-[#4b49ac] hover:underline">Voir calendrier</a>
                        </div>
                        
                        @if($upcomingEvents->count() > 0)
                            <div class="divide-y divide-gray-100">
                                @foreach($upcomingEvents as $event)
                                <div class="p-4 flex items-center hover:bg-gray-50 transition group">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex flex-col items-center justify-center mr-4 border border-indigo-100">
                                        <span class="text-[10px] font-bold uppercase tracking-wider">{{ \Carbon\Carbon::parse($event->start_date)->translatedFormat('M') }}</span>
                                        <span class="text-lg font-bold leading-none">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold text-gray-900 group-hover:text-[#4b49ac] transition-colors">{{ $event->title }}</h4>
                                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                            <span>{{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : 'Toute la journée' }}</span>
                                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                            <span class="uppercase bg-gray-100 px-2 py-0.5 rounded text-[10px] tracking-wide">{{ $event->activity_type }}</span>
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-10 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-gray-500 mb-2 text-sm">Votre calendrier est vide pour les prochains jours.</p>
                                <a href="{{ route('calendar.index') }}" class="text-[#4b49ac] font-medium hover:underline text-sm">+ Ajouter un événement</a>
                            </div>
                        @endif
                    </div>

                </div>

                {{-- COLONNE DROITE (Favoris / Quick Links) --}}
                <div class="lg:col-span-1 space-y-8">
                    
                    {{-- Widget Favoris --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full min-h-[400px]">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-gray-800">Accès Rapide</h3>
                        </div>
                        
                        <div class="flex space-x-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium cursor-pointer whitespace-nowrap">Tous</span>
                            <span class="px-3 py-1 text-gray-400 hover:bg-gray-50 rounded-full text-xs font-medium cursor-pointer whitespace-nowrap">Projets</span>
                            <span class="px-3 py-1 text-gray-400 hover:bg-gray-50 rounded-full text-xs font-medium cursor-pointer whitespace-nowrap">Dossiers</span>
                        </div>

                        {{-- Liste des favoris --}}
                        <div class="space-y-4">
                            @forelse($recentProjects->take(4) as $project)
                            <a href="{{ route('projects.show', $project) }}" class="group flex items-center p-3 rounded-lg border border-gray-100 hover:border-[#4b49ac] hover:shadow-sm transition cursor-pointer bg-gray-50 hover:bg-white">
                                <div class="w-8 h-8 rounded bg-white border border-gray-200 flex items-center justify-center mr-3 text-gray-500 font-bold text-xs" style="color: {{ $project->color ?? '#4b49ac' }}">
                                    {{ strtoupper(substr($project->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $project->name }}</p>
                                    <p class="text-[10px] text-gray-400">Projet • {{ $project->tasks_count }} tâches</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-[#4b49ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                            @empty
                            <div class="text-center py-10 opacity-50">
                                <div class="mb-2 text-3xl">⭐</div>
                                <p class="text-xs text-gray-500">Aucun favori pour le moment</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT POUR LE GRAPHIQUE --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('taskChart');
            
            if(ctx) {
                const todoCount = {{ $stats['todo'] }};
                const inProgressCount = {{ $stats['in_progress'] }};
                const doneCount = {{ $stats['done'] }};
                
                const total = todoCount + inProgressCount + doneCount;
                const isEmpty = total === 0;
                
                new Chart(ctx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['À faire', 'En cours', 'Terminées'],
                        datasets: [{
                            data: isEmpty ? [1] : [todoCount, inProgressCount, doneCount],
                            backgroundColor: isEmpty ? ['#F3F4F6'] : [
                                '#E5E7EB', // Gris (À faire)
                                '#4b49ac', // Violet (En cours)
                                '#10B981'  // Vert (Terminé)
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        cutout: '75%',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: !isEmpty }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>