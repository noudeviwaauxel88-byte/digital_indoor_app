<x-app-layout>
    <div x-data="{ 
        isSlideOverOpen: {{ $errors->any() && session('form_type') === 'create' ? 'true' : 'false' }}, 
        isViewModalOpen: false, 
        selectedEquipment: null 
    }">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Gestion du Stock</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('equipments.stockout.history') }}" class="px-4 py-2 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 shadow-sm hover:bg-gray-50 text-sm">
                    Historique Sorties
                </a>
                
                {{-- Formulaire de Recherche (Intitulé / Type / Marque) --}}
                <form action="{{ route('equipments.index') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Rechercher intitulé, type..." value="{{ request('search') }}" class="px-4 py-2 pl-10 border rounded-lg w-64 focus:ring-primary focus:border-primary">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('equipments.index') }}" class="text-xs text-gray-500 hover:underline">Réinitialiser</a>
                    @endif
                </form>

                <button @click="isSlideOverOpen = true" class="px-4 py-2 bg-primary text-white font-semibold rounded-lg shadow-md hover:bg-opacity-90">+ Nouveau</button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse ($equipments as $equipment)
                <div x-data="{ open: false }" class="relative bg-white rounded-lg shadow-sm overflow-hidden group border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                    
                    <button @click="selectedEquipment = {{ Js::from($equipment->load('availableItems')) }}; isViewModalOpen = true" class="w-full text-left focus:outline-none">
                        <div class="bg-white p-4 h-56 flex items-center justify-center"><img class="max-h-full max-w-full object-contain" src="{{ $equipment->image_path ? asset('storage/' . $equipment->image_path) : 'https://placehold.co/400x400/e2e8f0/e2e8f0?text=.' }}" alt="{{ $equipment->title }}"></div>
                    </button>
                    
                    <div class="p-4 border-t border-gray-100">
                        <h3 class="font-semibold text-gray-800 truncate">{{ $equipment->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $equipment->type }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <p class="text-lg font-bold text-gray-900">{{ number_format($equipment->price, 0, ',', ' ') }} FCFA</p>
                            
                            {{-- Quantité rouge si <= 3 --}}
                            <p class="text-sm font-semibold {{ $equipment->available_items_count <= 3 ? 'text-red-600' : 'text-gray-600' }}">
                                Qté: {{ $equipment->available_items_count }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="absolute top-2 right-2 z-10">
                        <button @click="open = !open" class="p-2 bg-white/70 rounded-full text-gray-600 hover:bg-white hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg></button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a href="{{ route('equipments.stockout.create', $equipment) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sortie de Stock</a>
                                <a href="{{ route('equipments.edit', $equipment) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Modifier</a>
                                <form method="POST" action="{{ route('equipments.destroy', $equipment) }}" onsubmit="return confirm('Êtes-vous sûr ?');">@csrf @method('DELETE')<button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Supprimer</button></form>
                            </div>
                        </div>
                    </div>
                    
                    @if($equipment->available_items_count == 0)
                        <div class="absolute top-2 left-2 px-2 py-1 bg-white/80 text-red-600 font-bold rounded-full text-xs uppercase tracking-wider shadow-sm">En rupture</div>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center py-12"><p class="text-gray-500">Aucun équipement trouvé.</p></div>
            @endforelse
        </div>

        <!-- SlideOver Création -->
        <div x-show="isSlideOverOpen" @keydown.escape.window="isSlideOverOpen = false" x-cloak class="relative z-10">
            <div x-show="isSlideOverOpen" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 overflow-hidden"><div class="absolute inset-0 overflow-hidden"><div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="isSlideOverOpen" @click.away="isSlideOverOpen = false"
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="pointer-events-auto w-screen max-w-md">
                    
                    <form method="POST" action="{{ route('equipments.store') }}" enctype="multipart/form-data" 
                          class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl"
                          x-data="{ quantity: {{ old('quantity', 0) }}, serials: {{ json_encode(old('serial_numbers', [])) }} }"> 
                        @csrf
                        <input type="hidden" name="form_type" value="create"> 
                        <div class="flex min-h-0 flex-1 flex-col overflow-y-scroll">
                            <div class="bg-primary py-6 px-4 sm:px-6">
                                <div class="flex items-center justify-between"><h2 class="text-base font-semibold leading-6 text-white">Ajouter un équipement</h2><div class="ml-3 flex h-7 items-center"><button @click="isSlideOverOpen = false" type="button" class="relative rounded-md bg-primary text-indigo-200 hover:text-white"><span class="sr-only">Close panel</span><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button></div></div>
                            </div>
                            <div class="relative mt-6 flex-1 px-4 sm:px-6">
                                <div class="space-y-6">
                                    <div><label for="image" class="block text-sm font-medium text-gray-900">Image</label><input type="file" name="image" id="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"><x-input-error :messages="$errors->get('image')" class="mt-2" /></div>
                                    <div>
                                        <label for="type" class="block text-sm font-medium text-gray-900">Type *</label>
                                        <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"><option value="Tablette">Tablette</option><option value="Panel">Panel</option><option value="All-in-one">All-in-one</option><option value="Camera">Camera</option><option value="Accessoire">Accessoire</option><option value="Ordinateur">Ordinateur</option><option value="Point d'accès">Point d'accès</option><option value="Audio">Audio</option></select>
                                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                                    </div>
                                    <div><label for="title" class="block text-sm font-medium text-gray-900">Intitulé *</label><input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"><x-input-error :messages="$errors->get('title')" class="mt-2" /></div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><label for="price" class="block text-sm font-medium text-gray-900">Prix (FCFA) *</label><input type="number" name="price" id="price" value="{{ old('price') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"></div>
                                        <div>
                                            <label for="quantity" class="block text-sm font-medium text-gray-900">Quantité *</label>
                                            <input type="number" name="quantity" id="quantity" 
                                                   x-model.number="quantity" 
                                                   min="0"
                                                   required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                                    
                                    <div>
                                        <label for="entry_date" class="block text-sm font-medium text-gray-900">Date d'entrée</label>
                                        <input type="date" name="entry_date" id="entry_date" value="{{ old('entry_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                        <x-input-error :messages="$errors->get('entry_date')" class="mt-2" />
                                    </div>
                                    <div><label for="brand" class="block text-sm font-medium text-gray-900">Marque</label><input type="text" name="brand" id="brand" value="{{ old('brand') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"></div>
                                    <div><label for="features" class="block text-sm font-medium text-gray-900">Caractéristiques</label><textarea name="features" id="features" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('features') }}</textarea></div>

                                    <div x-show="quantity > 0" class="space-y-4">
                                        <label class="block text-sm font-medium text-gray-900">Numéros de Série</label>
                                        <template x-for="(serial, index) in Array.from({ length: quantity })" :key="index">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-500 w-8 text-right" x-text="index + 1 + '.'"></span>
                                                <input type="text" 
                                                       :name="'serial_numbers[' + index + ']'"
                                                       :value="serials[index] || ''"
                                                       placeholder="Entrer un numéro de série..."
                                                       required 
                                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                            </div>
                                        </template>
                                        <x-input-error :messages="$errors->get('serial_numbers')" class="mt-2" />
                                        <x-input-error :messages="$errors->get('serial_numbers.*')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-shrink-0 justify-end px-4 py-4">
                            <button @click="isSlideOverOpen = false" type="button" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Annuler</button>
                            <button type="submit" class="ml-4 inline-flex justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-opacity-90">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div></div></div>
        </div>

        <!-- Modal Visualisation -->
        <div x-show="isViewModalOpen" @keydown.escape.window="isViewModalOpen = false" x-cloak class="relative z-20">
            <div x-show="isViewModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="isViewModalOpen" @click.away="isViewModalOpen = false"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <div class="flex justify-between items-center"><h3 class="text-base font-semibold leading-6 text-gray-900" x-text="selectedEquipment ? selectedEquipment.title : ''"></h3><button @click="isViewModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button></div>
                                    <div class="mt-4">
                                        <div class="flex justify-center mb-4" x-show="selectedEquipment && selectedEquipment.image_path"><img class="h-40 w-auto object-contain rounded-md" :src="selectedEquipment ? '/storage/' + selectedEquipment.image_path : ''" alt="Image de l'équipement"></div>
                                        <dl class="divide-y divide-gray-100">
                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"><dt class="text-sm font-medium leading-6 text-gray-900">Intitulé</dt><dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0" x-text="selectedEquipment ? selectedEquipment.title : ''"></dd></div>
                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"><dt class="text-sm font-medium leading-6 text-gray-900">Prix</dt><dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0" x-text="selectedEquipment ? new Intl.NumberFormat('fr-FR').format(selectedEquipment.price) + ' FCFA' : ''"></dd></div>
                                            
                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Quantité</dt>
                                                <dd class="mt-1 text-sm font-semibold sm:col-span-2 sm:mt-0" 
                                                    :class="selectedEquipment && selectedEquipment.available_items_count <= 3 ? 'text-red-600' : 'text-gray-700'"
                                                    x-text="selectedEquipment ? selectedEquipment.available_items_count : ''"></dd>
                                            </div>
                                            
                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Date d'entrée</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0" 
                                                    x-text="selectedEquipment && selectedEquipment.entry_date ? new Date(selectedEquipment.entry_date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric'}) : 'N/A'">
                                                </dd>
                                            </div>
                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"><dt class="text-sm font-medium leading-6 text-gray-900">Marque</dt><dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0" x-text="selectedEquipment ? selectedEquipment.brand : 'N/A'"></dd></div>
                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"><dt class="text-sm font-medium leading-6 text-gray-900">Caractéristiques</dt><dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0" x-text="selectedEquipment ? selectedEquipment.features : 'Aucune'"></dd></div>

                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Articles en stock</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <ul x-show="selectedEquipment && selectedEquipment.available_items.length > 0" class="list-disc list-inside space-y-1">
                                                        <template x-for="item in selectedEquipment.available_items" :key="item.id">
                                                            <li x-text="item.serial_number"></li>
                                                        </template>
                                                    </ul>
                                                    <span x-show="!selectedEquipment || selectedEquipment.available_items.length == 0">Aucun</span>
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"><button @click="isViewModalOpen = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Fermer</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>