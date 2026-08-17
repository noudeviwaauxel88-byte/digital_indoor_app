<x-app-layout>
    
    <!-- Conteneur principal avec l'état Alpine.js pour le panneau latéral -->
    <div x-data="{ isSlideOverOpen: {{ $errors->any() ? 'true' : 'false' }}, activeTab: 'informations' }">

        <div class="flex gap-8 px-6 sm:px-10 py-8">
            <!-- ======================== -->
            <!-- == Contenu Principal  == -->
            <!-- ======================== -->
            <div class="flex-1">
                <!-- Header du contenu -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Tous les projets</h1>
                    <div class="flex items-center gap-4">
                        
                        {{-- SÉCURITÉ : Bouton Créer Projet visible uniquement pour Admin et Manager --}}
                        @hasanyrole('SuperAdmin|Manager')
                        <button @click="isSlideOverOpen = true" class="px-4 py-2 bg-[#4b49ac] text-white font-semibold rounded-lg shadow-md hover:bg-opacity-90 flex items-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Créer projet
                        </button>
                        @endhasanyrole

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" placeholder="Rechercher des projets" class="pl-10 px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#4b49ac] focus:border-[#4b49ac] hidden sm:block">
                        </div>
                    </div>
                </div>

                <!-- Liste des Projets -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
                        <span class="text-sm font-medium text-gray-500">{{ count($projects) }} projet(s) en cours</span>
                    </div>
                    
                    @forelse ($projects as $project)
                        <div x-data="{ open: false }" class="relative flex items-center p-4 gap-4 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition-colors group">
                            <!-- Lien vers le détail du projet (Accessible à tous) -->
                            <a href="{{ route('projects.show', $project) }}" class="flex flex-1 items-center gap-4 min-w-0 group-hover:translate-x-1 transition-transform">
                                {{-- Avatar Projet --}}
                                <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center text-sm font-bold text-white shadow-sm" style="background-color: {{ $project->color ?? '#4b49ac' }};">
                                    {{ strtoupper(substr($project->name, 0, 2)) }}
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-gray-800 truncate">{{ $project->name }}</p>
                                        @if($project->structure)
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 border border-gray-200 rounded text-[10px] font-bold uppercase tracking-wide flex-shrink-0">{{ $project->structure }}</span>
                                        @endif
                                        @if($project->visibility === 'private')
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-0.5 truncate w-3/4">{{ $project->description ?? 'Aucune description' }}</p>
                                </div>
                            </a>
                            
                            <div class="hidden md:flex items-center gap-6">
                                <div class="w-32 text-sm text-gray-500 text-right">
                                    @if($project->start_date)
                                        <div class="flex items-center justify-end gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Avatars des membres --}}
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

                            {{-- Menu Options (Sécurisé) --}}
                            {{-- On affiche le menu 3 points UNIQUEMENT si l'utilisateur a le droit de modifier ou supprimer --}}
                            @hasanyrole('SuperAdmin|Manager')
                            <div class="relative">
                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 11-4 0 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-cloak 
                                     class="absolute bottom-full right-0 mb-2 z-50 w-48 origin-bottom-right rounded-lg bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none transform transition-all"
                                     style="display: none;">
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
                            <h3 class="text-lg font-medium text-gray-900">Aucun projet</h3>
                            <p class="text-gray-500 mt-1 mb-6 max-w-sm">Commencez par créer un nouveau projet pour collaborer avec votre équipe.</p>
                            
                            {{-- SÉCURITÉ : Bouton Vide également protégé --}}
                            @hasanyrole('SuperAdmin|Manager')
                            <button @click="isSlideOverOpen = true" class="px-4 py-2 bg-[#4b49ac] text-white rounded-lg shadow hover:bg-opacity-90 transition-colors">Créer mon premier projet</button>
                            @endhasanyrole
                        </div>
                    @endforelse
                </div>
            </div>
            
            <aside class="w-72 flex-shrink-0 hidden xl:block"></aside>
        </div>

        <!-- Panneau Latéral de Création (Seuls SuperAdmin et Manager peuvent techniquement l'ouvrir via le bouton, mais le code reste ici pour l'UX) -->
        <div x-show="isSlideOverOpen" @keydown.escape.window="isSlideOverOpen = false" x-cloak class="relative z-50">
            <div x-show="isSlideOverOpen" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="isSlideOverOpen" @click.away="isSlideOverOpen = false" 
                             x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" 
                             class="pointer-events-auto w-screen max-w-md">
                            
                            <form method="POST" action="{{ route('projects.store') }}" class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-2xl">
                                @csrf
                                
                                <div class="flex min-h-0 flex-1 flex-col overflow-y-scroll">
                                    <!-- Header du Panneau -->
                                    <div class="bg-[#4b49ac] py-6 px-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <h2 class="text-lg font-semibold text-white">Créer un nouveau projet</h2>
                                            <button @click="isSlideOverOpen = false" type="button" class="relative rounded-md text-indigo-200 hover:text-white focus:outline-none">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                        <p class="mt-1 text-sm text-indigo-200">Remplissez les informations ci-dessous.</p>
                                    </div>

                                    <!-- Onglets -->
                                    <div class="border-b border-gray-200 bg-white sticky top-0 z-10">
                                        <nav class="-mb-px flex px-6" aria-label="Tabs">
                                            <button @click="activeTab = 'informations'" type="button" :class="activeTab === 'informations' ? 'border-[#4b49ac] text-[#4b49ac]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="w-1/2 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-center transition-colors">
                                                Informations
                                            </button>
                                            <button @click="activeTab = 'inviter'" type="button" :class="activeTab === 'inviter' ? 'border-[#4b49ac] text-[#4b49ac]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="w-1/2 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-center transition-colors">
                                                Inviter des membres
                                            </button>
                                        </nav>
                                    </div>

                                    <!-- Contenu du formulaire -->
                                    <div class="relative mt-6 flex-1 px-4 sm:px-6 pb-6">
                                        
                                        <!-- Onglet 1: Informations -->
                                        <div x-show="activeTab === 'informations'" class="space-y-6">
                                            <div>
                                                <label for="name" class="block text-sm font-medium text-gray-900">Nom du projet *</label>
                                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm" placeholder="Ex: Refonte site web" required>
                                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                            </div>
                                            
                                            <div>
                                                <label for="structure" class="block text-sm font-medium text-gray-900">Structure / Département</label>
                                                <input type="text" name="structure" id="structure" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm" placeholder="Ex: DHI, Marketing...">
                                            </div>
                                            
                                            <div>
                                                <label for="start_date" class="block text-sm font-medium text-gray-900">Date de début</label>
                                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm">
                                            </div>

                                            <div>
                                                <label for="description" class="block text-sm font-medium text-gray-900">Description</label>
                                                <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm" placeholder="Décrivez brièvement le projet...">{{ old('description') }}</textarea>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900 mb-2">Visibilité</label>
                                                <div class="space-y-3">
                                                    <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                                        <input type="radio" name="visibility" value="private" checked class="h-4 w-4 text-[#4b49ac] border-gray-300 focus:ring-[#4b49ac] mt-0.5">
                                                        <div class="ml-3">
                                                            <span class="block text-sm font-medium text-gray-900">Privé</span>
                                                            <span class="block text-xs text-gray-500">Seuls les membres invités peuvent voir ce projet.</span>
                                                        </div>
                                                    </label>
                                                    <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                                        <input type="radio" name="visibility" value="public" class="h-4 w-4 text-[#4b49ac] border-gray-300 focus:ring-[#4b49ac] mt-0.5">
                                                        <div class="ml-3">
                                                            <span class="block text-sm font-medium text-gray-900">Public</span>
                                                            <span class="block text-xs text-gray-500">Visible par toute l'organisation.</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Onglet 2: Inviter (SÉLECTION MULTIPLE CORRIGÉE) -->
                                        <div x-show="activeTab === 'inviter'" class="space-y-6"
                                             x-data="{
                                                 search: '',
                                                 selectedUsers: [],
                                                 users: {{ Js::from($users ?? []) }}, // Liste des utilisateurs injectée par le contrôleur
                                                 
                                                 // Filtrer les utilisateurs
                                                 get filteredUsers() {
                                                     if (this.search === '') return [];
                                                     const lowerQuery = this.search.toLowerCase();
                                                     
                                                     return this.users.filter(u => {
                                                         // Créer le nom complet pour la recherche
                                                         const fullName = (u.firstname + ' ' + u.lastname).toLowerCase();
                                                         // Vérifier si ça matche la recherche (Nom complet ou Email)
                                                         const matches = fullName.includes(lowerQuery) || u.email.toLowerCase().includes(lowerQuery);
                                                         // Vérifier si l'utilisateur n'est pas déjà sélectionné
                                                         const notSelected = !this.selectedUsers.find(selected => selected.id === u.id);
                                                         
                                                         return matches && notSelected;
                                                     });
                                                 },
                                                 
                                                 // Ajouter un utilisateur
                                                 select(user) {
                                                     this.selectedUsers.push(user);
                                                     this.search = ''; // Réinitialiser la barre de recherche
                                                 },
                                                 
                                                 // Retirer un utilisateur
                                                 remove(index) {
                                                     this.selectedUsers.splice(index, 1);
                                                 }
                                             }">
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900 mb-1">Rechercher des collègues</label>
                                                <p class="text-xs text-gray-500 mb-3">Tapez le nom, prénom ou email pour ajouter plusieurs membres.</p>
                                                
                                                <!-- Champ de recherche -->
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                                    </div>
                                                    <input type="text" x-model="search" class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-[#4b49ac] focus:ring-[#4b49ac] sm:text-sm" placeholder="Ex: Jean Dupont...">
                                                    
                                                    <!-- Liste déroulante des résultats -->
                                                    <div x-show="search.length > 0" class="absolute z-20 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                                        <template x-if="filteredUsers.length === 0">
                                                            <div class="px-4 py-2 text-sm text-gray-500">Aucun utilisateur trouvé (ou déjà ajouté).</div>
                                                        </template>
                                                        
                                                        <template x-for="user in filteredUsers" :key="user.id">
                                                            <div @click="select(user)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 flex items-center gap-3 border-b border-gray-50 last:border-0">
                                                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                                                    <span x-text="user.firstname ? user.firstname.charAt(0) : user.name.charAt(0)"></span>
                                                                </div>
                                                                <div>
                                                                    <span class="block truncate font-medium text-gray-900" x-text="user.firstname + ' ' + user.lastname"></span>
                                                                    <span class="block truncate text-xs text-gray-500" x-text="user.email"></span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- Liste des Utilisateurs Sélectionnés -->
                                                <div x-show="selectedUsers.length > 0" class="mt-4 space-y-2">
                                                    <template x-for="(user, index) in selectedUsers" :key="user.id">
                                                        <div class="flex items-center justify-between p-2 bg-indigo-50 rounded-lg border border-indigo-100">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                                                    <span x-text="user.firstname.charAt(0)"></span>
                                                                </div>
                                                                <div>
                                                                    <span class="block text-sm font-medium text-gray-900" x-text="user.firstname + ' ' + user.lastname"></span>
                                                                    <span class="block text-[10px] text-indigo-400">Invité</span>
                                                                </div>
                                                            </div>
                                                            <button type="button" @click="remove(index)" class="text-red-400 hover:text-red-600 p-1 rounded-full hover:bg-red-50" title="Retirer">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            </button>
                                                            
                                                            <!-- INPUTS CACHÉS : C'est ici que la magie opère pour envoyer le tableau d'IDs -->
                                                            <input type="hidden" name="invited_users[]" :value="user.id">
                                                        </div>
                                                    </template>
                                                </div>
                                                
                                                <div x-show="selectedUsers.length === 0" class="mt-4 text-center py-4 border-2 border-dashed border-gray-200 rounded-lg text-gray-400 text-sm">
                                                    Aucun membre invité pour le moment.
                                                </div>

                                            </div>

                                            <div class="bg-gray-50 p-4 rounded-md">
                                                <h4 class="text-sm font-medium text-gray-900">Note sur les invitations</h4>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Les utilisateurs sélectionnés verront immédiatement ce projet dans leur tableau de bord.
                                                </p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                
                                <!-- Footer Actions -->
                                <div class="flex flex-shrink-0 justify-end px-4 py-4 bg-gray-50 border-t border-gray-200 gap-3">
                                    <button @click="isSlideOverOpen = false" type="button" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Annuler</button>
                                    <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-[#4b49ac] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#403d94] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Créer le projet</button>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>