<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('equipments.index') }}" class="flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Retour à la liste du stock
            </a>
        </div>
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Sortie de Stock : {{ $equipment->title }}</h1>

        <div class="bg-white shadow-sm rounded-lg p-6 sm:p-8">
            <div class="flex items-center gap-6 mb-8 pb-8 border-b border-gray-200">
                <img class="h-24 w-24 object-contain rounded-md bg-gray-100 p-2 flex-shrink-0" src="{{ $equipment->image_path ? asset('storage/' . $equipment->image_path) : 'https://placehold.co/100x100/e2e8f0/e2e8f0?text=.' }}" alt="{{ $equipment->title }}">
                <div>
                    <h2 class="text-lg font-semibold">{{ $equipment->title }}</h2>
                    <p class="text-sm text-gray-500">{{ $equipment->type }} - {{ $equipment->brand }}</p>
                    <p class="mt-2 font-bold text-gray-700">Articles en stock: {{ $equipment->availableItems->count() }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('equipments.stockout.store', $equipment) }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="movement_date" class="block text-sm font-medium text-gray-700">Date de sortie *</label>
                            <input type="date" name="movement_date" id="movement_date" value="{{ old('movement_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <x-input-error :messages="$errors->get('movement_date')" class="mt-2" />
                        </div>
                        
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Demandeur *</label>
                            <select id="user_id" name="user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">Sélectionner un utilisateur</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', Auth::id()) == $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div x-data="{ 
                            query: '', 
                            selectedProjectId: null, 
                            selectedProjectName: '',
                            projects: {{ Js::from($projects) }},
                            isOpen: false,
                            
                            get filteredProjects() {
                                if (this.query === '') return this.projects;
                                return this.projects.filter(project => 
                                    project.name.toLowerCase().includes(this.query.toLowerCase())
                                );
                            },
                            selectProject(project) {
                                this.selectedProjectId = project.id;
                                this.selectedProjectName = project.name;
                                this.query = project.name;
                                this.isOpen = false;
                            }
                        }" class="relative">
                            
                            <label class="block text-sm font-medium text-gray-700">Projet (Rechercher)</label>
                            
                            <div class="relative mt-1">
                                <input type="text" 
                                       x-model="query"
                                       @focus="isOpen = true"
                                       @click.away="isOpen = false"
                                       @keydown.escape="isOpen = false"
                                       placeholder="Commencez à taper le nom..."
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                
                                <input type="hidden" name="project_id" :value="selectedProjectId">
                                
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                            </div>

                            <ul x-show="isOpen && filteredProjects.length > 0" 
                                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                                style="display: none;">
                                <template x-for="project in filteredProjects" :key="project.id">
                                    <li @click="selectProject(project)" 
                                        class="relative cursor-pointer select-none py-2 pl-3 pr-9 text-gray-900 hover:bg-primary hover:text-white">
                                        <span x-text="project.name" class="block truncate font-normal"></span>
                                    </li>
                                </template>
                            </ul>
                            
                            <div x-show="isOpen && filteredProjects.length === 0" class="absolute z-10 mt-1 w-full rounded-md bg-white p-4 text-center shadow-lg border text-gray-500 text-sm">
                                Aucun projet trouvé.
                            </div>
                            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="other_destination" class="block text-sm font-medium text-gray-700">Autre / Destination</label>
                            <input type="text" name="other_destination" id="other_destination" value="{{ old('other_destination') }}" placeholder="Ex: Maintenance, Don, Perte..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <x-input-error :messages="$errors->get('other_destination')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Articles à sortir *</label>
                        @if($equipment->availableItems->count() > 0)
                            <div class="mt-2 p-4 border border-gray-200 rounded-md max-h-60 overflow-y-auto">
                                <div class="space-y-3">
                                    @foreach($equipment->availableItems as $item)
                                        <div class="flex items-center">
                                            <input id="item_{{ $item->id }}" name="item_ids[]" value="{{ $item->id }}" type="checkbox" @checked(is_array(old('item_ids')) && in_array($item->id, old('item_ids'))) class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                            <label for="item_{{ $item->id }}" class="ml-3 block text-sm text-gray-900">{{ $item->serial_number }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('item_ids')" class="mt-2" />
                        @else
                            <p class="mt-2 text-sm text-red-600">Cet article est en rupture de stock.</p>
                        @endif
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">Motif / Détails supplémentaires</label>
                        <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('reason') }}</textarea>
                    </div>
                    <div>
                        <label for="document" class="block text-sm font-medium text-gray-900">Joindre un fichier (PDF, Word)</label>
                        <input type="file" name="document" id="document" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-4">
                    <a href="{{ route('equipments.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Annuler</a>
                    <button type="submit" class="inline-flex justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-opacity-90 disabled:opacity-50" @disabled($equipment->availableItems->count() == 0)>Enregistrer la sortie</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>