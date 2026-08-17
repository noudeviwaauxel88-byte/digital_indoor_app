<x-app-layout>
    {{-- FullCalendar & Alpine Plugins --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    {{-- STYLE PERSONNALISÉ ULTRA-COMPACT --}}
    <style>
        /* Réduire l'espace global */
        .fc-header-toolbar {
            margin-bottom: 1rem !important;
            padding: 0 0.5rem;
        }
        
        /* Titre (Novembre 2025) plus petit */
        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            color: #111827;
        }

        /* Boutons de navigation (< > Aujourd'hui) plus petits */
        .fc-button-group .fc-button {
            background-color: white !important;
            border: 1px solid #e5e7eb !important;
            color: #374151 !important;
            box-shadow: none !important;
            font-weight: 500;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.8rem !important;
        }
        .fc-button-group .fc-button:hover {
            background-color: #f9fafb !important;
        }
        .fc-today-button {
            background-color: white !important;
            border: 1px solid #e5e7eb !important;
            color: #374151 !important;
            text-transform: capitalize;
            border-radius: 0.375rem !important;
            margin-left: 0.5rem !important;
            font-weight: 500 !important;
            opacity: 1 !important;
            padding: 0.25rem 0.75rem !important;
            font-size: 0.8rem !important;
        }

        /* Boutons de vue (Mois, Semaine, Jour, Liste) */
        .fc-toolbar-chunk:last-child {
            background: #f3f4f6;
            padding: 2px;
            border-radius: 0.5rem;
            display: flex;
            gap: 2px;
        }
        
        .fc-toolbar-chunk:last-child .fc-button {
            background: transparent !important;
            border: none !important;
            color: #6b7280 !important;
            border-radius: 0.375rem !important;
            font-size: 0.75rem !important;
            padding: 0.25rem 0.75rem !important;
            font-weight: 500;
            box-shadow: none !important;
        }

        /* Bouton Actif */
        .fc-toolbar-chunk:last-child .fc-button-active {
            background-color: white !important;
            color: #111827 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e5e7eb !important;
        }

        /* Grille du calendrier */
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: #f3f4f6;
        }
        .fc-col-header-cell {
            padding: 0.5rem 0;
            background-color: #f9fafb;
            color: #9ca3af;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: none !important;
        }
        
        /* === LE PLUS IMPORTANT : COMPACTER LES CELLULES === */
        .fc-daygrid-day-frame {
            min-height: 80px !important; /* Force une hauteur minimale réduite */
            height: auto !important;
        }
        
        .fc-daygrid-day-top {
            justify-content: flex-end;
            padding-right: 0.25rem;
            padding-top: 0.25rem;
        }
        .fc-daygrid-day-number {
            font-size: 0.8rem;
            color: #374151;
            text-decoration: none !important;
        }
        
        /* Événements compacts */
        .fc-event {
            border: none !important;
            padding: 1px 3px;
            font-size: 0.7rem;
            border-radius: 3px;
            cursor: pointer;
            margin-bottom: 1px !important;
            line-height: 1.2;
        }
    </style>

    <div x-data="calendarApp()" x-init="initCalendar()" class="min-h-screen bg-white">
        
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end mb-2">
                <button @click="openCreateModal()" class="px-3 py-2 bg-primary text-white rounded-lg shadow hover:bg-opacity-90 font-medium text-sm">
                    + Ajouter un événement
                </button>
            </div>

            {{-- Conteneur du Calendrier --}}
            <div class="bg-white">
                <div id='calendar' x-ref="calendarEl"></div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODALE CRÉATION / ÉDITION --}}
        {{-- ============================================================== --}}
        <div x-show="isFormModalOpen" style="display: none;" class="relative z-50">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" @click.away="isFormModalOpen = false">
                        
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 border-b">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-bold text-gray-900" x-text="isEditMode ? 'Modifier l\'événement' : 'Ajouter un événement'"></h3>
                                <button @click="isFormModalOpen = false" class="text-gray-400 hover:text-gray-500 text-2xl">&times;</button>
                            </div>
                        </div>

                        {{-- Formulaire --}}
                        <form x-bind:action="isEditMode ? '/calendrier/' + form.id : '{{ route('calendar.store') }}'" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                            @csrf
                            <template x-if="isEditMode">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            {{-- Titre --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom de l'événement *</label>
                                <input type="text" name="title" x-model="form.title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary">
                            </div>

                            {{-- Dates --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date début *</label>
                                    <input type="date" name="start_date" x-model="form.start_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Heure début</label>
                                    <input type="time" name="start_time" x-model="form.start_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date fin *</label>
                                    <input type="date" name="end_date" x-model="form.end_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Heure fin</label>
                                    <input type="time" name="end_time" x-model="form.end_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary">
                                </div>
                            </div>

                            {{-- Projet (Recherche) --}}
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700">Projet associé</label>
                                <input type="text" x-model="projectQuery" @input="isProjectListOpen = true" @click.away="isProjectListOpen = false" placeholder="Rechercher un projet..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary">
                                <input type="hidden" name="project_id" x-model="form.project_id">
                                
                                <ul x-show="isProjectListOpen && filteredProjects.length > 0" class="absolute z-10 mt-1 max-h-40 w-full overflow-auto rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                                    <template x-for="p in filteredProjects" :key="p.id">
                                        <li @click="selectProject(p)" class="cursor-pointer px-4 py-2 hover:bg-gray-100 text-sm" x-text="p.name"></li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Type d'activité --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type d'activité *</label>
                                <select name="activity_type" x-model="form.activity_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary">
                                    <option value="reunion">Réunion (Bleu)</option>
                                    <option value="formation">Formation (Vert)</option>
                                    <option value="mission">Mission (Orange)</option>
                                    <option value="autre">Autre (Gris)</option>
                                </select>
                            </div>

                            {{-- CHAMPS DYNAMIQUES --}}
                            <div x-show="['reunion', 'formation'].includes(form.activity_type)" class="p-4 bg-gray-50 rounded border">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Participants / Invités</label>
                                <select name="participants_ids[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm h-24">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" :selected="form.participants_ids.includes({{ $user->id }})">
                                            {{ $user->firstname }} {{ $user->lastname }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Maintenez CTRL pour sélectionner plusieurs.</p>
                            </div>

                            <div x-show="form.activity_type === 'formation'" class="p-4 bg-green-50 rounded border border-green-100 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Formateur</label>
                                    <input type="text" name="trainer" x-model="form.trainer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Modules</label>
                                    <textarea name="modules" x-model="form.modules" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                </div>
                            </div>

                            <div x-show="form.activity_type === 'mission'" class="p-4 bg-orange-50 rounded border border-orange-100 space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Intervenant</label>
                                        <input type="text" name="intervenant" x-model="form.intervenant" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Institution</label>
                                        <input type="text" name="institution" x-model="form.institution" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lieu</label>
                                    <input type="text" name="location" x-model="form.location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" x-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Document (PDF, Word)</label>
                                <input type="file" name="document" class="mt-1 block w-full text-sm text-gray-500 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-primary/10 file:text-primary">
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t">
                                <button type="button" @click="isFormModalOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 shadow-sm hover:bg-gray-50">Annuler</button>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md shadow-sm hover:bg-opacity-90" x-text="isEditMode ? 'Mettre à jour' : 'Créer l\'événement'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- MODALE DÉTAILS (Lecture Seule) --}}
        {{-- ========================================== --}}
        <div x-show="isDetailModalOpen" style="display: none;" class="relative z-50">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    {{-- MODIFICATION ICI : Bordure arrondie et ombre comme sur la capture --}}
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md" @click.away="isDetailModalOpen = false">
                        
                        {{-- HEADER SIMPLE --}}
                        <div class="px-6 pt-6 pb-2 flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                {{-- Point de couleur --}}
                                <span class="w-4 h-4 rounded-full" :style="'background-color:' + viewEvent.raw.backgroundColor"></span>
                                <h3 class="text-2xl font-bold text-gray-900" x-text="viewEvent.title"></h3>
                            </div>
                            <button @click="isDetailModalOpen = false" class="text-gray-400 hover:text-gray-500 text-xl font-bold">&times;</button>
                        </div>

                        {{-- CONTENU ÉPURÉ --}}
                        <div class="px-6 py-4 space-y-4 text-gray-600">
                            
                            {{-- Date et Heure --}}
                            <div class="flex items-center gap-3 text-sm">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <div>
                                    <span class="font-medium text-gray-900" x-text="formatDateOnly(viewEvent.start)"></span>
                                    <span class="text-gray-500 text-xs ml-2" x-text="formatTime(viewEvent.start) + ' - ' + formatTime(viewEvent.end)"></span>
                                </div>
                            </div>

                            <div class="h-px bg-gray-100 w-full"></div>

                            {{-- Description / Type --}}
                            <div class="flex items-start gap-3 text-sm">
                                <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                <div>
                                    <p class="font-medium text-gray-900 capitalize" x-text="viewEvent.activity_type"></p>
                                    <p class="text-xs text-gray-500" x-text="viewEvent.description || 'Aucune description'"></p>
                                </div>
                            </div>

                            <div class="h-px bg-gray-100 w-full"></div>

                            {{-- Participants --}}
                            <div class="flex items-center gap-3 text-sm">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">Participants</span>
                                </div>
                                {{-- Badge style "J" pour Jean --}}
                                <template x-if="viewEvent.participants_count > 0">
                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-500 text-white text-xs font-bold" x-text="viewEvent.participants_count"></span>
                                </template>
                            </div>

                            <div class="h-px bg-gray-100 w-full"></div>

                            {{-- Calendrier par défaut --}}
                            <div class="flex items-center gap-3 text-sm">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="font-medium text-gray-900">Calendrier par défaut</span>
                                <span class="ml-auto text-purple-500"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg></span>
                            </div>
                        </div>

                        {{-- FOOTER BOUTONS --}}
                        <div class="px-6 pb-6 pt-2 flex gap-3 justify-between">
                            {{-- Bouton Supprimer (Rouge pâle) --}}
                            <form x-bind:action="'/calendrier/' + viewEvent.id" method="POST" onsubmit="return confirm('Supprimer cet événement ?');" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-50 text-red-500 font-medium rounded-lg hover:bg-red-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Supprimer
                                </button>
                            </form>
                            
                            {{-- Bouton Modifier (Violet) --}}
                            <button @click="openEditModal()" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-[#8b5cf6] text-white font-medium rounded-lg hover:bg-[#7c3aed] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Modifier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function calendarApp() {
            return {
                isFormModalOpen: false,
                isDetailModalOpen: false,
                isEditMode: false,
                isProjectListOpen: false,
                projectQuery: '',
                
                projects: {{ Js::from($projects) }},
                get filteredProjects() {
                    if (this.projectQuery === '') return this.projects;
                    return this.projects.filter(p => p.name.toLowerCase().includes(this.projectQuery.toLowerCase()));
                },

                form: {
                    id: null,
                    title: '',
                    start_date: '',
                    start_time: '',
                    end_date: '',
                    end_time: '',
                    activity_type: 'reunion',
                    project_id: null,
                    description: '',
                    trainer: '',
                    modules: '',
                    intervenant: '',
                    institution: '',
                    location: '',
                    participants_ids: [],
                    file_path: null
                },

                viewEvent: {},

                initCalendar() {
                    var calendarEl = this.$refs.calendarEl;
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'fr',
                        firstDay: 1,
                        height: 'auto', // <-- AJUSTEMENT AUTOMATIQUE DE LA HAUTEUR
                        contentHeight: 'auto',
                        headerToolbar: {
                            start: 'title prev,next today',
                            center: '',
                            end: 'dayGridMonth,timeGridWeek,timeGridDay listMonth'
                        },
                        buttonText: {
                            today: 'Aujourd\'hui',
                            month: 'Mois',
                            week: 'Semaine',
                            day: 'Jour',
                            list: 'Liste'
                        },
                        events: {{ Js::from($events) }},
                        eventClick: (info) => {
                            this.showEventDetails(info.event);
                        }
                    });
                    calendar.render();
                },

                showEventDetails(event) {
                    const props = event.extendedProps;
                    const participants = props.participants_ids || [];
                    
                    this.viewEvent = {
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end || event.start,
                        activity_type: props.activity_type,
                        description: props.description,
                        project_name: props.project ? props.project.name : null,
                        location: props.location,
                        institution: props.institution,
                        file_path: props.file_path,
                        participants_count: participants.length,
                        raw: {
                            ...props,
                            id: event.id,
                            title: event.title,
                            start: event.start,
                            end: event.end,
                            backgroundColor: event.backgroundColor // Pour le point de couleur
                        }
                    };
                    this.isDetailModalOpen = true;
                },

                openCreateModal() {
                    this.resetForm();
                    this.isEditMode = false;
                    this.isFormModalOpen = true;
                },

                openEditModal() {
                    this.isDetailModalOpen = false;
                    this.isEditMode = true;
                    const data = this.viewEvent.raw;
                    const startDate = new Date(data.start);
                    const endDate = data.end ? new Date(data.end) : startDate;

                    this.form = {
                        id: data.id,
                        title: data.title,
                        start_date: startDate.toISOString().split('T')[0],
                        start_time: startDate.getHours() ? startDate.toTimeString().slice(0,5) : '',
                        end_date: endDate.toISOString().split('T')[0],
                        end_time: (data.end && data.end.getHours()) ? endDate.toTimeString().slice(0,5) : '',
                        activity_type: data.activity_type,
                        project_id: data.project_id,
                        description: data.description,
                        trainer: data.trainer,
                        modules: data.modules,
                        intervenant: data.intervenant,
                        institution: data.institution,
                        location: data.location,
                        participants_ids: data.participants_ids || [],
                        file_path: data.file_path
                    };

                    if (data.project) {
                        this.projectQuery = data.project.name;
                    }
                    this.isFormModalOpen = true;
                },

                resetForm() {
                    this.form = {
                        id: null, title: '', start_date: '', start_time: '', end_date: '', end_time: '',
                        activity_type: 'reunion', project_id: null, description: '', trainer: '', 
                        modules: '', intervenant: '', institution: '', location: '', participants_ids: []
                    };
                    this.projectQuery = '';
                },

                selectProject(p) {
                    this.form.project_id = p.id;
                    this.projectQuery = p.name;
                    this.isProjectListOpen = false;
                },

                formatDateOnly(date) {
                    if(!date) return '';
                    return new Date(date).toLocaleDateString('fr-FR', { 
                        day: 'numeric', month: 'short', year: 'numeric' 
                    });
                },
                
                formatTime(date) {
                    if(!date) return '';
                    const d = new Date(date);
                    // Si l'heure est 00:00:00, on n'affiche rien ou une valeur par défaut
                    if (d.getHours() === 0 && d.getMinutes() === 0) return '00:00';
                    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                }
            }
        }
    </script>
</x-app-layout>