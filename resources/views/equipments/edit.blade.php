<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('equipments.index') }}" class="flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Retour à la liste du stock
            </a>
        </div>
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Modifier l'équipement : {{ $equipment->title }}</h1>

        <div class="bg-white shadow-sm rounded-lg p-6 sm:p-8">
            {{-- Initialisation d'Alpine avec les items existants --}}
            <form method="POST" action="{{ route('equipments.update', $equipment) }}" enctype="multipart/form-data"
                  x-data="{ 
                      items: {{ Js::from($equipment->items) }},
                      deletedItemIds: [],
                      addItem() {
                          this.items.push({ id: null, serial_number: '' });
                      },
                      removeItem(index) {
                          let item = this.items[index];
                          if (item.id) {
                              this.deletedItemIds.push(item.id);
                          }
                          this.items.splice(index, 1);
                      }
                  }">
                @csrf
                @method('PATCH')
                
                {{-- Champ caché pour stocker les IDs à supprimer --}}
                <input type="hidden" name="deleted_item_ids" :value="deletedItemIds.join(',')">

                <div class="space-y-6">
                    {{-- Section Image --}}
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-900">Image</label>
                        <input type="file" name="image" id="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        @if($equipment->image_path)
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-2">Image actuelle :</p>
                                <img src="{{ asset('storage/' . $equipment->image_path) }}" alt="{{ $equipment->title }}" class="h-24 w-auto rounded-md border border-gray-200">
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>

                    {{-- Section Infos Générales --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-900">Type *</label>
                            <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                                @foreach(['Tablette', 'Panel', 'All-in-one', 'Camera', 'Accessoire', 'Ordinateur', 'Point d\'accès', 'Audio'] as $type)
                                    <option value="{{ $type }}" @selected(old('type', $equipment->type) == $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-900">Intitulé *</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $equipment->title) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-900">Prix (FCFA) *</label>
                            <input type="number" name="price" id="price" value="{{ old('price', $equipment->price) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>
                        
                        {{-- CHAMP QUANTITÉ EN LECTURE SEULE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Quantité Totale</label>
                            <div class="mt-1 flex items-center">
                                <input type="text" readonly :value="items.length" class="block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm focus:border-gray-200 focus:ring-0 cursor-not-allowed">
                                <span class="ml-3 text-xs text-gray-500">Calculé automatiquement</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-900">Marque</label>
                            <input type="text" name="brand" id="brand" value="{{ old('brand', $equipment->brand) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label for="entry_date" class="block text-sm font-medium text-gray-900">Date d'entrée</label>
                            <input type="date" name="entry_date" id="entry_date" value="{{ old('entry_date', optional($equipment->entry_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>
                    </div>

                    <div>
                        <label for="features" class="block text-sm font-medium text-gray-900">Caractéristiques</label>
                        <textarea name="features" id="features" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">{{ old('features', $equipment->features) }}</textarea>
                    </div>

                    {{-- ==================================================== --}}
                    {{-- == SECTION GESTION DES NUMÉROS DE SÉRIE == --}}
                    {{-- ==================================================== --}}
                    <div class="border-t pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Gestion des Articles (Numéros de série)</h3>
                            <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary bg-primary/10 hover:bg-primary/20 focus:outline-none">
                                + Ajouter un article
                            </button>
                        </div>

                        <div class="space-y-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-400 text-sm w-6" x-text="index + 1 + '.'"></span>
                                    
                                    {{-- Input caché pour l'ID (si existant) --}}
                                    <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id">
                                    
                                    <div class="flex-grow">
                                        <input type="text" 
                                               :name="'items[' + index + '][serial_number]'" 
                                               x-model="item.serial_number"
                                               placeholder="Numéro de série" 
                                               required
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                    </div>

                                    {{-- Bouton Supprimer --}}
                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 p-2" title="Supprimer cet article">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>
                            
                            <div x-show="items.length === 0" class="text-center text-gray-500 text-sm py-4">
                                Aucun article enregistré. Cliquez sur "Ajouter" pour commencer.
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('items')" class="mt-2" />
                    </div>
                    {{-- ==================================================== --}}

                </div>

                <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-4">
                    <a href="{{ route('equipments.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-opacity-90">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>