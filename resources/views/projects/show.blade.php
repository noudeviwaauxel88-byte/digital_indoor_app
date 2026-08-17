<x-app-layout>
    <div x-data="{ 
        isCreateModalOpen: {{ $errors->any() ? 'true' : 'false' }}, 
        isEditModalOpen: false,
        isShowModalOpen: false,
        selectedTask: null,
        
        // CORRECTION IMPORTANTE : On utilise la variable $users (Tous les employés) 
        // au lieu de $project->members pour permettre d'assigner n'importe qui.
        projectMembers: {{ Js::from($users) }}.map(u => ({
            id: u.id,
            name: u.firstname ? (u.firstname + ' ' + u.lastname) : u.name,
            email: u.email,
            initial: (u.firstname ? u.firstname.charAt(0) : u.name.charAt(0)).toUpperCase()
        })),

        // Définition des Statuts avec Icônes et Textes (Français)
        statuses: {
            'todo': { label: 'Non commencé', icon: `<svg class='w-4 h-4 text-gray-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' stroke-dasharray='4 4' /></svg>` },
            'in_progress': { label: 'En cours', icon: `<svg class='w-4 h-4 text-blue-500 animate-spin' fill='none' viewBox='0 0 24 24'><circle class='opacity-25' cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4'></circle><path class='opacity-75' fill='currentColor' d='M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z'></path></svg>` },
            'to_validate': { label: 'À valider', icon: `<svg class='w-4 h-4 text-orange-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>` },
            'done': { label: 'Achevée', icon: `<svg class='w-4 h-4 text-green-500' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'/></svg>` },
            'cancelled': { label: 'Annulée', icon: `<svg class='w-4 h-4 text-gray-400' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'/></svg>` }
        },

        // Définition des Priorités avec Icônes (Barres de signal)
        priorities: {
            'low': { label: 'Basse', icon: `<svg class='w-4 h-4 text-gray-500' viewBox='0 0 24 24' fill='currentColor'><rect x='2' y='14' width='4' height='6' rx='1'/><rect x='8' y='10' width='4' height='10' rx='1' class='text-gray-200'/><rect x='14' y='6' width='4' height='14' rx='1' class='text-gray-200'/></svg>` },
            'normal': { label: 'Moyenne', icon: `<svg class='w-4 h-4 text-blue-500' viewBox='0 0 24 24' fill='currentColor'><rect x='2' y='14' width='4' height='6' rx='1'/><rect x='8' y='10' width='4' height='10' rx='1'/><rect x='14' y='6' width='4' height='14' rx='1' class='text-gray-200'/></svg>` },
            'high': { label: 'Élevée', icon: `<svg class='w-4 h-4 text-orange-500' viewBox='0 0 24 24' fill='currentColor'><rect x='2' y='14' width='4' height='6' rx='1'/><rect x='8' y='10' width='4' height='10' rx='1'/><rect x='14' y='6' width='4' height='14' rx='1'/></svg>` },
            'urgent': { label: 'Urgent', icon: `<svg class='w-4 h-4 text-red-600' viewBox='0 0 24 24' fill='currentColor'><path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'/></svg>` }
        },

        formatDate(date) {
            if(!date) return 'Non définie';
            return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
        }
    }">

        <!-- Header de la page projet -->
        <div class="p-6 sm:p-10">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-800 truncate uppercase">{{ $project->name }}</h1>
                <div class="flex items-center gap-4">
                    
                    {{-- SÉCURITÉ : Bouton Créer Tâche visible uniquement pour Admin et Manager --}}
                    @hasanyrole('SuperAdmin|Manager')
                    <button @click="isCreateModalOpen = true" class="px-4 py-2 bg-primary text-white font-semibold rounded-lg shadow-md hover:bg-opacity-90 flex items-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Créer une tâche
                    </button>
                    @endhasanyrole

                    <input type="text" placeholder="Rechercher des tâches" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                </div>
            </div>

            <div class="mt-8 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <a href="#" class="border-primary text-primary whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Liste</a>
                </nav>
            </div>
        </div>

        <!-- Tableau KANBAN -->
        <div class="px-6 sm:px-10 pb-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            @php($columns = ['todo' => 'Non commencé', 'in_progress' => 'En cours', 'to_validate' => 'À valider', 'done' => 'Achevée', 'cancelled' => 'Annulée'])
            @foreach ($columns as $status => $title)
            <div class="bg-[#F4F5F7] rounded-lg p-3 h-full">
                <div class="flex justify-between items-center mb-3 px-1">
                    <h3 class="font-semibold text-gray-600 text-sm flex items-center gap-2">
                        @if($status == 'todo') <span class="w-4 h-4 border-2 border-gray-400 border-dotted rounded-full"></span>
                        @elseif($status == 'in_progress') <span class="w-4 h-4 border-2 border-gray-600 border-t-transparent rounded-full animate-spin-slow"></span>
                        @elseif($status == 'done') <svg class="w-4 h-4 text-white bg-green-500 rounded-full p-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        @else <span class="w-4 h-4 bg-gray-300 rounded-full"></span> @endif
                        {{ $title }} 
                        <span class="text-gray-400 font-normal ml-1">({{ $project->tasks->where('status', $status)->count() }})</span>
                    </h3>
                    
                    {{-- SÉCURITÉ : Bouton '+' visible uniquement pour Admin et Manager --}}
                    @hasanyrole('SuperAdmin|Manager')
                    <button @click="isCreateModalOpen = true" class="text-gray-400 hover:text-gray-600 text-xl leading-none">+</button>
                    @endhasanyrole
                </div>
                
                <div class="space-y-3 min-h-[50px]">
                    @foreach($project->tasks->where('status', $status) as $task)
                        <div @click="isShowModalOpen = true; selectedTask = {{ json_encode($task->load('assignees', 'project')) }}" 
                             class="cursor-pointer bg-white p-3 rounded shadow-sm border border-transparent hover:border-primary group transition-all">
                            
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] text-gray-400 font-mono uppercase">TASK-{{ $task->id }}</span>
                                
                                @if($task->assignees->count() > 0)
                                    <div class="flex -space-x-1">
                                        @foreach($task->assignees->take(3) as $assignee)
                                            <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold border border-white" title="{{ $assignee->firstname }} {{ $assignee->lastname }}">
                                                {{ substr($assignee->firstname ?? $assignee->name, 0, 1) }}
                                            </div>
                                        @endforeach
                                        @if($task->assignees->count() > 3)
                                            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-[10px] font-bold border border-white">+{{ $task->assignees->count() - 3 }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <p class="font-medium text-gray-800 text-sm mb-2 line-clamp-2">{{ $task->title }}</p>
                            
                            <div class="flex items-center gap-2 mt-2">
                                @if($task->priority == 'urgent') <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[10px] rounded font-medium">URGENT</span>
                                @elseif($task->priority == 'high') <span class="px-2 py-0.5 bg-orange-100 text-orange-600 text-[10px] rounded font-medium">HAUTE</span>
                                @endif
                                
                                @if($status == 'done') <span class="px-2 py-0.5 bg-green-100 text-green-600 text-[10px] rounded font-medium">ACHEVÉ</span>
                                @elseif($status == 'in_progress') <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] rounded font-medium">EN COURS</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- ========================================================== -->
        <!-- == MODAL CRÉATION TÂCHE == -->
        <!-- ========================================================== -->
        <div x-show="isCreateModalOpen" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="isCreateModalOpen" class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div @click.away="isCreateModalOpen = false" class="relative w-full max-w-lg transform rounded-lg bg-white text-left shadow-xl transition-all">
                        
                        <!-- Header -->
                        <div class="px-6 pt-6 pb-2 flex justify-between items-start">
                            <h3 class="text-xl font-bold text-gray-900">Créer une nouvelle tâche</h3>
                            <button @click="isCreateModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Formulaire -->
                        <form method="POST" action="{{ route('tasks.store', $project) }}" enctype="multipart/form-data" class="px-6 pb-6 space-y-5" 
                              x-data="{ 
                                  status: 'todo', 
                                  priority: 'normal', 
                                  assigneeQuery: '', 
                                  selectedUsers: [],
                                  isAssigneeOpen: false, 
                                  
                                  get filteredUsers() { 
                                      if (this.assigneeQuery === '') return projectMembers; 
                                      const lowerQuery = this.assigneeQuery.toLowerCase();
                                      return projectMembers.filter(u => 
                                          u.name.toLowerCase().includes(lowerQuery) && 
                                          !this.selectedUsers.some(su => su.id === u.id)
                                      ); 
                                  }, 
                                  selectUser(user) { 
                                      this.selectedUsers.push(user);
                                      this.assigneeQuery = ''; 
                                      this.isAssigneeOpen = false;
                                  },
                                  removeUser(index) {
                                      this.selectedUsers.splice(index, 1);
                                  }
                              }">
                            @csrf

                            <!-- Description -->
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Description</label><div class="relative rounded-md border border-gray-200 shadow-sm focus-within:border-purple-500 focus-within:ring-1 focus-within:ring-purple-500"><textarea name="title" rows="3" class="block w-full border-0 bg-transparent p-3 text-sm placeholder-gray-400 focus:ring-0 resize-none" placeholder="Entrez une description..." required></textarea></div></div>
                            
                            <!-- Projet -->
                            <div class="flex items-center text-sm py-1"><span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">PROJET</span><div class="flex items-center gap-2 w-2/3 text-gray-700 font-medium"><div class="w-4 h-4 border border-green-500 rounded flex items-center justify-center text-[9px] text-green-600 font-bold">P</div>{{ $project->name }}</div></div>

                            <!-- Statut (Corrigé avec Icones) -->
                            <input type="hidden" name="status" x-model="status">
                            <div class="flex items-center text-sm relative" x-data="{ isOpen: false }">
                                <span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">STATUT</span>
                                <div class="w-2/3 relative">
                                    <button type="button" @click="isOpen = !isOpen" @click.away="isOpen = false" class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 py-2 px-3 rounded-md text-left flex items-center gap-2 transition-colors text-sm border border-transparent focus:border-purple-300">
                                        <span class="flex items-center gap-2">
                                            <span x-html="statuses[status].icon"></span>
                                            <span x-text="statuses[status].label"></span>
                                        </span>
                                    </button>
                                    <div x-show="isOpen" class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-100 py-1" style="display: none;">
                                        <template x-for="(data, key) in statuses" :key="key">
                                            <button type="button" @click="status = key; isOpen = false" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm text-gray-700">
                                                <span x-html="data.icon"></span>
                                                <span x-text="data.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- CESSIONNAIRES (MULTI-SELECT avec tous les utilisateurs) -->
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center text-sm relative">
                                    <span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">CESSIONNAIRES</span>
                                    <div class="w-2/3 relative">
                                        <!-- Champ de sélection -->
                                        <div @click="isAssigneeOpen = true; $nextTick(() => $refs.searchInput.focus())" @click.away="isAssigneeOpen = false" class="w-full bg-gray-50 hover:bg-gray-100 border border-transparent focus-within:border-indigo-300 rounded-md p-2 min-h-[38px] flex flex-wrap gap-2 cursor-text">
                                            <template x-for="(user, index) in selectedUsers" :key="user.id">
                                                <div class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs flex items-center gap-1">
                                                    <span x-text="user.initial"></span> <span x-text="user.name"></span>
                                                    <button type="button" @click.stop="removeUser(index)" class="hover:text-indigo-900 font-bold">&times;</button>
                                                    <input type="hidden" name="assignees[]" :value="user.id">
                                                </div>
                                            </template>
                                            <span x-show="selectedUsers.length === 0" class="text-gray-400 text-sm">Sélectionner...</span>
                                        </div>

                                        <!-- Dropdown -->
                                        <div x-show="isAssigneeOpen" class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-100 py-2" style="display: none;">
                                            <div class="px-3 pb-2 border-b border-gray-100"><input x-ref="searchInput" x-model="assigneeQuery" type="text" class="w-full border bg-gray-50 rounded px-2 py-1 text-xs focus:ring-0" placeholder="Recherche..."></div>
                                            <div class="max-h-48 overflow-y-auto mt-1">
                                                <template x-for="user in filteredUsers" :key="user.id"><button type="button" @click="selectUser(user)" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm"><div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold"><span x-text="user.initial"></span></div><span class="text-gray-800" x-text="user.name"></span></button></template>
                                                <div x-show="filteredUsers.length === 0" class="px-3 py-2 text-gray-400 text-center text-xs">Aucun autre membre trouvé</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Priorité (Corrigé avec Icones) -->
                            <input type="hidden" name="priority" x-model="priority">
                            <div class="flex items-center text-sm relative" x-data="{ isPrioOpen: false }">
                                <span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">PRIORITÉ</span>
                                <div class="w-2/3 relative">
                                    <button type="button" @click="isPrioOpen = !isPrioOpen" @click.away="isPrioOpen = false" class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 py-2 px-3 rounded-md text-left flex items-center gap-2 transition-colors text-sm border border-transparent focus:border-purple-300">
                                        <span class="flex items-center gap-2">
                                            <span x-html="priorities[priority].icon"></span>
                                            <span x-text="priorities[priority].label"></span>
                                        </span>
                                    </button>
                                    <div x-show="isPrioOpen" class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-100 py-1" style="display: none;">
                                        <template x-for="(data, key) in priorities" :key="key">
                                            <button type="button" @click="priority = key; isPrioOpen = false" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm text-gray-700">
                                                <span x-html="data.icon"></span>
                                                <span x-text="data.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500 font-medium w-1/3 uppercase">STRUCTURE</span><input type="text" name="structure" class="w-2/3 bg-transparent border-0 border-b border-gray-200 focus:border-purple-500 focus:ring-0 px-0 py-1 text-gray-800 placeholder-gray-400" placeholder="ex: DHI"></div>
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500 font-medium w-1/3 uppercase">MODULE</span><input type="text" name="module" class="w-2/3 bg-transparent border-0 border-b border-gray-200 focus:border-purple-500 focus:ring-0 px-0 py-1 text-gray-800 placeholder-gray-400" placeholder="ex: Formation"></div>
                            
                            <!-- DATES (Corrigé avec Labels) -->
                            <div class="flex items-start justify-between text-sm">
                                <span class="text-gray-500 font-medium w-1/3 uppercase pt-2">DATES</span>
                                <div class="w-2/3 grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-400 font-semibold mb-0.5">DÉBUT</label>
                                        <input type="date" name="start_date" class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded px-2 py-1 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 font-semibold mb-0.5">FIN</label>
                                        <input type="date" name="due_date" class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded px-2 py-1 text-xs">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm pt-2"><span class="text-gray-500 font-medium w-1/3 uppercase">PIÈCE JOINTE</span><input type="file" name="document" class="w-2/3 text-xs text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700"></div>

                            <div class="flex justify-between items-center pt-6 mt-4 border-t border-gray-100"><button @click="isCreateModalOpen = false" type="button" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 font-bold text-sm transition-colors">Annuler</button><button type="submit" class="bg-[#8b5cf6] text-white px-6 py-2 rounded-lg hover:bg-[#7c3aed] font-bold text-sm shadow-md transition-colors">Créer la tâche</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- == MODAL DÉTAILS TÂCHE (Lecture Seule) == -->
        <!-- ========================================================== -->
        <div x-show="isShowModalOpen" x-cloak class="relative z-30">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div @click.away="isShowModalOpen = false" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                        
                        <div class="px-6 pt-6 pb-2 flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <span class="w-4 h-4 rounded-full" :class="{'bg-red-500': selectedTask && selectedTask.priority === 'urgent', 'bg-orange-500': selectedTask && selectedTask.priority === 'high', 'bg-blue-400': selectedTask && selectedTask.priority === 'normal', 'bg-gray-400': selectedTask && selectedTask.priority === 'low'}"></span>
                                <h3 class="text-2xl font-bold text-gray-900 line-clamp-1" x-text="selectedTask ? selectedTask.title : ''"></h3>
                            </div>
                            <button @click="isShowModalOpen = false" class="text-gray-400 hover:text-gray-500 text-2xl font-light">&times;</button>
                        </div>

                        <template x-if="selectedTask">
                            <div class="px-6 py-4 space-y-5 text-gray-600">
                                <div class="flex items-center gap-3 text-sm"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><div><span class="font-medium text-gray-900">Échéance : </span><span x-text="selectedTask.due_date ? formatDate(selectedTask.due_date) : 'Non définie'"></span></div></div>
                                <div class="h-px bg-gray-100 w-full"></div>
                                
                                <div class="flex items-center gap-3 text-sm">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span class="font-medium text-gray-900">Assignés :</span>
                                    <div class="flex -space-x-1">
                                        <template x-for="assignee in selectedTask.assignees">
                                            <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold border border-white" :title="assignee.firstname + ' ' + assignee.lastname">
                                                <span x-text="assignee.firstname ? assignee.firstname.charAt(0) : assignee.name.charAt(0)"></span>
                                            </div>
                                        </template>
                                        <template x-if="(!selectedTask.assignees || selectedTask.assignees.length === 0)">
                                            <span class="text-xs text-gray-500 ml-2">Aucun</span>
                                        </template>
                                    </div>
                                </div>
                                
                                <template x-if="selectedTask.document_path"><div class="flex items-center gap-3 text-sm mt-2"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg><a :href="`/storage/${selectedTask.document_path}`" target="_blank" class="text-primary hover:underline font-medium">Voir le document joint</a></div></template>
                            </div>
                        </template>

                        <div class="px-6 pb-6 pt-2 flex gap-3 justify-between">
                            {{-- SÉCURITÉ : Boutons Modifier/Supprimer visibles uniquement pour Admin et Manager --}}
                            @hasanyrole('SuperAdmin|Manager')
                            <form :action="`/tasks/${selectedTask ? selectedTask.id : ''}`" method="POST" onsubmit="return confirm('Supprimer cette tâche ?');" class="flex-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-50 text-red-500 font-medium rounded-lg hover:bg-red-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Supprimer</button>
                            </form>
                            <button @click="isEditModalOpen = true; isShowModalOpen = false" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-[#8b5cf6] text-white font-medium rounded-lg hover:bg-[#7c3aed] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">Modifier</button>
                            @endhasanyrole

                            {{-- Message pour les employés --}}
                            @unlessrole('SuperAdmin|Manager')
                                <p class="text-xs text-gray-400 text-center w-full italic">Lecture seule (Contactez votre manager pour modifier)</p>
                            @endunlessrole
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- == MODAL MODIFIER TÂCHE (AVEC NOUVEAUX CHAMPS) == -->
        <!-- ========================================================== -->
        <div x-show="isEditModalOpen" x-cloak class="relative z-40">
             <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div @click.away="isEditModalOpen = false" class="relative w-full max-w-lg transform rounded-lg bg-white text-left shadow-xl transition-all">
                        
                        <template x-if="selectedTask">
                            <form method="POST" :action="`/tasks/${selectedTask.id}`" enctype="multipart/form-data" 
                                  class="px-6 pb-6 space-y-5"
                                  x-data="{
                                      status: selectedTask.status,
                                      priority: selectedTask.priority,
                                      assigneeQuery: '',
                                      selectedUsers: selectedTask.assignees ? selectedTask.assignees.map(u => ({
                                          id: u.id,
                                          name: u.firstname + ' ' + u.lastname,
                                          initial: u.firstname ? u.firstname.charAt(0) : u.name.charAt(0)
                                      })) : [],
                                      isAssigneeOpen: false,
                                      
                                      get filteredUsers() {
                                          if (this.assigneeQuery === '') return projectMembers;
                                          const lowerQuery = this.assigneeQuery.toLowerCase();
                                          return projectMembers.filter(u => 
                                              u.name.toLowerCase().includes(lowerQuery) && 
                                              !this.selectedUsers.some(su => su.id === u.id)
                                          );
                                      },
                                      selectUser(user) {
                                          this.selectedUsers.push(user);
                                          this.assigneeQuery = '';
                                          this.isAssigneeOpen = false;
                                      },
                                      removeUser(index) {
                                          this.selectedUsers.splice(index, 1);
                                      }
                                  }">
                                @csrf
                                @method('PATCH')

                                <div class="bg-white pt-6 pb-4 flex justify-between items-start border-b border-gray-100 mb-4">
                                    <h3 class="text-xl font-bold leading-6 text-gray-900">Modifier la tâche</h3>
                                    <button @click="isEditModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                </div>

                                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Description</label><div class="relative rounded-md border border-gray-200 shadow-sm focus-within:border-purple-500 focus-within:ring-1 focus-within:ring-purple-500"><textarea name="title" rows="3" class="block w-full border-0 bg-transparent p-3 text-sm focus:ring-0 resize-none" x-model="selectedTask.title" required></textarea></div></div>
                                <div class="flex items-center text-sm py-1"><span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">PROJET</span><div class="flex items-center gap-2 w-2/3 text-gray-700 font-medium"><div class="w-4 h-4 border border-green-500 rounded flex items-center justify-center text-[9px] text-green-600 font-bold">P</div>{{ $project->name }}</div></div>

                                <!-- Statut (Edit) -->
                                <input type="hidden" name="status" x-model="status">
                                <div class="flex items-center text-sm relative" x-data="{ isOpen: false }">
                                    <span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">STATUT</span>
                                    <div class="w-2/3 relative">
                                        <button type="button" @click="isOpen = !isOpen" @click.away="isOpen = false" class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 py-2 px-3 rounded-md text-left flex items-center gap-2 transition-colors text-sm border border-transparent focus:border-purple-300">
                                            <span class="flex items-center gap-2">
                                                <span x-html="statuses[status].icon"></span>
                                                <span x-text="statuses[status].label"></span>
                                            </span>
                                        </button>
                                        <div x-show="isOpen" class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-100 py-1" style="display: none;">
                                            <template x-for="(data, key) in statuses" :key="key">
                                                <button type="button" @click="status = key; isOpen = false" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm text-gray-700">
                                                    <span x-html="data.icon"></span>
                                                    <span x-text="data.label"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- CESSIONNAIRES EDIT -->
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center text-sm relative">
                                        <span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">CESSIONNAIRES</span>
                                        <div class="w-2/3 relative">
                                            <div @click="isAssigneeOpen = true" @click.away="isAssigneeOpen = false" class="w-full bg-gray-50 hover:bg-gray-100 border border-transparent focus-within:border-indigo-300 rounded-md p-2 min-h-[38px] flex flex-wrap gap-2 cursor-text">
                                                <template x-for="(user, index) in selectedUsers" :key="user.id">
                                                    <div class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs flex items-center gap-1">
                                                        <span x-text="user.initial"></span> <span x-text="user.name"></span>
                                                        <button type="button" @click.stop="removeUser(index)" class="hover:text-indigo-900 font-bold">&times;</button>
                                                        <input type="hidden" name="assignees[]" :value="user.id">
                                                    </div>
                                                </template>
                                                <span x-show="selectedUsers.length === 0" class="text-gray-400 text-sm">Sélectionner...</span>
                                            </div>
                                            <div x-show="isAssigneeOpen" class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-100 py-2" style="display: none;">
                                                <div class="px-3 pb-2 border-b border-gray-100"><input x-model="assigneeQuery" type="text" class="w-full border bg-gray-50 rounded px-2 py-1 text-xs focus:ring-0" placeholder="Recherche..."></div>
                                                <div class="max-h-48 overflow-y-auto mt-1">
                                                    <template x-for="user in filteredUsers" :key="user.id"><button type="button" @click="selectUser(user)" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm"><span class="text-gray-800" x-text="user.name"></span></button></template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Priorité (Edit) -->
                                <input type="hidden" name="priority" x-model="priority">
                                <div class="flex items-center text-sm relative" x-data="{ isPrioOpen: false }">
                                    <span class="text-gray-500 text-xs font-semibold w-1/3 uppercase">PRIORITÉ</span>
                                    <div class="w-2/3 relative">
                                        <button type="button" @click="isPrioOpen = !isPrioOpen" @click.away="isPrioOpen = false" class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 py-2 px-3 rounded-md text-left flex items-center gap-2 transition-colors text-sm border border-transparent focus:border-purple-300">
                                            <span class="flex items-center gap-2">
                                                <span x-html="priorities[priority].icon"></span>
                                                <span x-text="priorities[priority].label"></span>
                                            </span>
                                        </button>
                                        <div x-show="isPrioOpen" class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-100 py-1" style="display: none;">
                                            <template x-for="(data, key) in priorities" :key="key">
                                                <button type="button" @click="priority = key; isPrioOpen = false" class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm text-gray-700">
                                                    <span x-html="data.icon"></span>
                                                    <span x-text="data.label"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-sm"><span class="text-gray-500 font-medium w-1/3 uppercase">STRUCTURE</span><input type="text" name="structure" class="w-2/3 bg-transparent border-0 border-b border-gray-200 focus:border-purple-500 focus:ring-0 px-0 py-1 text-gray-800" x-model="selectedTask.structure"></div>
                                <div class="flex items-center justify-between text-sm"><span class="text-gray-500 font-medium w-1/3 uppercase">MODULE</span><input type="text" name="module" class="w-2/3 bg-transparent border-0 border-b border-gray-200 focus:border-purple-500 focus:ring-0 px-0 py-1 text-gray-800" x-model="selectedTask.module"></div>
                                
                                <!-- DATES (Edit) -->
                                <div class="flex items-start justify-between text-sm">
                                    <span class="text-gray-500 font-medium w-1/3 uppercase pt-2">DATES</span>
                                    <div class="w-2/3 grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] text-gray-400 font-semibold mb-0.5">DÉBUT</label>
                                            <input type="date" name="start_date" class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded px-2 py-1 text-xs" x-model="selectedTask.start_date">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-gray-400 font-semibold mb-0.5">FIN</label>
                                            <input type="date" name="due_date" class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded px-2 py-1 text-xs" x-model="selectedTask.due_date">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-sm pt-2"><span class="text-gray-500 font-medium w-1/3 uppercase">PIÈCE JOINTE</span><input type="file" name="document" class="w-2/3 text-xs text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700"></div>

                                <div class="flex justify-between items-center pt-6 mt-4 border-t border-gray-100">
                                    <button @click="isEditModalOpen = false" type="button" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 font-bold text-sm transition-colors">Annuler</button>
                                    <button type="submit" class="bg-[#8b5cf6] text-white px-6 py-2 rounded-lg hover:bg-[#7c3aed] font-bold text-sm shadow-md transition-colors">Enregistrer</button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>