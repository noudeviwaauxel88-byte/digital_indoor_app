<x-app-layout>
    <div x-data="{ isSlideOverOpen: {{ $errors->any() ? 'true' : 'false' }}, activeTab: 'informations' }">

        <!-- Banner de notification Flash avec auto-disparition après 4 secondes -->
        @if (session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="mx-6 sm:mx-10 mt-4 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
            </div>
        @endif

        <div class="flex gap-8 px-6 sm:px-10 py-8">
            <div class="flex-1">
                <!-- Header et Filtres -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Tous les projets</h1>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filtres par Statut -->
                        <form method="GET" action="{{ route('projects.index') }}" class="flex items-center gap-2">
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-lg focus:ring-[#4b49ac] focus:border-[#4b49ac] py-2 pl-3 pr-8">
                                <option value="">Tous les statuts</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>En retard</option>
                            </select>
                        </form>

                        <!-- Barre de recherche -->
                        <form method="GET" action="{{ route('projects.index') }}" class="relative">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="pl-9 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-[#4b49ac] focus:border-[#4b49ac]">
                        </form>

                        @hasanyrole('SuperAdmin|Manager')
                        <button @click="isSlideOverOpen = true" class="px-4 py-2 bg-[#4b49ac] text-white font-semibold rounded-lg shadow-md hover:bg-opacity-90 flex items-center gap-2 transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Créer projet
                        </button>
                        @endhasanyrole
                    </div>
                </div>

                <!-- Liste des Projets -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">{{ count($projects) }} projet(s) trouvé(s)</span>
                        @if(request('status') || request('search'))
                            <a href="{{ route('projects.index') }}" class="text-xs text-[#4b49ac] hover:underline font-semibold">Réinitialiser les filtres</a>
                        @endif
                    </div>
                    
                    @forelse ($projects as $project)
                        <div x-data="{ open: false }" class="relative flex items-center p-4 gap-4 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition-colors group">
                            <a href="{{ route('projects.show', $project) }}" class="flex flex-1 items-center gap-4 min-w-0">
                                <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center text-sm font-bold text-white shadow-sm" style="background-color: {{ $project->color ?? '#4b49ac' }};">
                                    {{ strtoupper(substr($project->name, 0, 2)) }}
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-semibold text-gray-800 truncate">{{ $project->name }}</p>
                                        
                                        <!-- Badges Statut -->
                                        @if($project->status === 'completed')
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded">Terminé</span>
                                        @elseif($project->end_date && \Carbon\Carbon::parse($project->end_date)->isPast())
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded">En retard</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded">En cours</span>
                                        @endif

                                        @if($project->structure)
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 border border-gray-200 rounded text-[10px] font-bold uppercase tracking-wide">{{ $project->structure }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-0.5 truncate w-3/4">{{ $project->description ?? 'Aucune description' }}</p>
                                </div>
                            </a>

                            <!-- Lien Document Joint -->
                            @if($project->file_path)
                                <a href="{{ route('projects.document', $project) }}" target="_blank" class="hidden sm:flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    <span>Document joint</span>
                                </a>
                            @endif
                            
                            <div class="hidden md:flex items-center gap-6">
                                <div class="w-32 text-sm text-gray-500 text-right">
                                    @if($project->start_date)
                                        <div class="flex items-center justify-end gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex items-center -space-x-2">
                                    @foreach($project->members->take(3) as $member)
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shadow-sm" title="{{ $member->firstname }} {{ $member->lastname }}">
                                            {{ strtoupper(substr($member->firstname, 0, 1)) }}
                                        </div>
                                    @endforeach
                                    @if(count($project->members) > 3)
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-bold shadow-sm">+{{ count($project->members) - 3 }}</div>
                                    @endif
                                </div>
                            </div>

                            @hasanyrole('SuperAdmin|Manager')
                            <div class="relative">
                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 11-4 0 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-cloak 
                                     class="absolute bottom-full right-0 mb-2 z-50 w-48 origin-bottom-right rounded-lg bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <div class="py-1">
                                        <a href="{{ route('projects.edit', $project) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Modifier
                                        </a>
                                        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endhasanyrole
                        </div>
                    @empty
                        <div class="text-center p-12 flex flex-col items-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Aucun projet trouvé</h3>
                            <p class="text-gray-500 mt-1 mb-6 max-w-sm">Ajustez vos filtres ou créez un nouveau projet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Panneau Latéral de Création (avec support d'upload de document) -->
        <div x-show="isSlideOverOpen" @keydown.escape.window="isSlideOverOpen = false" x-cloak class="relative z-50">
            <div x-show="isSlideOverOpen" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="isSlideOverOpen" class="pointer-events-auto w-screen max-w-md">
                            
                            <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data" class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-2xl">
                                @csrf
                                <div class="flex min-h-0 flex-1 flex-col overflow-y-scroll">
                                    <div class="bg-[#4b49ac] py-6 px-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <h2 class="text-lg font-semibold text-white">Créer un nouveau projet</h2>
                                            <button @click="isSlideOverOpen = false" type="button" class="text-indigo-200 hover:text-white">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="relative mt-6 flex-1 px-4 sm:px-6 pb-6 space-y-6">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-900">Nom du projet *</label>
                                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm" required>
                                        </div>
                                        
                                        <div>
                                            <label for="structure" class="block text-sm font-medium text-gray-900">Structure / Département</label>
                                            <input type="text" name="structure" id="structure" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm">
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="start_date" class="block text-sm font-medium text-gray-900">Date de début</label>
                                                <input type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm">
                                            </div>
                                            <div>
                                                <label for="end_date" class="block text-sm font-medium text-gray-900">Date d'échéance</label>
                                                <input type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm">
                                            </div>
                                        </div>

                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-900">Description</label>
                                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm"></textarea>
                                        </div>

                                        <div>
                                            <label for="document" class="block text-sm font-medium text-gray-900">Document joint (optionnel)</label>
                                            <input type="file" name="document" id="document" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-[#4b49ac] hover:file:bg-indigo-100">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-900 mb-2">Visibilité</label>
                                            <div class="space-y-2">
                                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="radio" name="visibility" value="private" checked class="text-[#4b49ac] focus:ring-[#4b49ac]"> Privé
                                                </label>
                                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                                    <input type="radio" name="visibility" value="public" class="text-[#4b49ac] focus:ring-[#4b49ac]"> Public
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end px-4 py-4 bg-gray-50 gap-3">
                                    <button @click="isSlideOverOpen = false" type="button" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700">Annuler</button>
                                    <button type="submit" class="rounded-md bg-[#4b49ac] px-4 py-2 text-sm font-medium text-white hover:bg-opacity-90">Créer le projet</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>