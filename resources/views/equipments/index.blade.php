<x-app-layout>
    <div class="py-6 px-4 sm:px-6 lg:px-8" x-data="{ isSlideOverOpen: false }">
        
        <!-- En-tête de la page -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Gestion du Stock</h1>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Bouton Historique Sorties -->
                <a href="{{ route('equipments.stockout.history') }}" class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 shadow-sm hover:bg-gray-50 text-sm">
                    Historique Sorties
                </a>

                <!-- Bouton + Type Équipement -->
                <button @click="$dispatch('open-modal-add-type')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm text-sm transition">
                    + Type Équipement
                </button>
                
                <!-- Barre de recherche -->
                <form action="{{ route('equipments.index') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Rechercher intitulé, type..." value="{{ request('search') }}" class="px-4 py-2 pl-9 border border-gray-300 rounded-lg w-56 sm:w-64 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </form>

                <!-- Bouton + Nouveau -->
                <button @click="isSlideOverOpen = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm text-sm transition">
                    + Nouveau
                </button>
            </div>
        </div>

        <!-- Grille des équipements (Style exact d'origine) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($equipments as $equipment)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between relative group hover:shadow-md transition">
                    
                    <div>
                        <!-- Menu d'actions (Trois points vertical) -->
                        <div class="absolute top-3 right-3 z-10" x-data="{ openMenu: false }">
                            <button @click="openMenu = !openMenu" @click.away="openMenu = false" class="p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="openMenu" 
                                 x-transition 
                                 class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-100 py-1 text-xs z-20"
                                 style="display: none;">
                                
                                <a href="{{ route('equipments.stockout.create', $equipment) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    Sortie de Stock
                                </a>
                                
                                <a href="{{ route('equipments.edit', $equipment) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    Modifier
                                </a>

                                <form action="{{ route('equipments.destroy', $equipment) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de cet équipement ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-50">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Image de l'équipement (issue du Type) -->
                        <div class="w-full h-44 bg-gray-100 flex items-center justify-center p-4">
                            @if($equipment->equipmentType && $equipment->equipmentType->image)
                                <img src="{{ asset('storage/' . $equipment->equipmentType->image) }}" 
                                     alt="{{ $equipment->name }}" 
                                     class="max-h-full max-w-full object-contain">
                            @else
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center rounded text-gray-400 font-semibold text-sm">
                                    {{ $equipment->name }}
                                </div>
                            @endif
                        </div>

                        <!-- Contenu texte de la carte -->
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 text-sm mb-0.5 truncate" title="{{ $equipment->name }}">
                                {{ $equipment->name }}
                            </h3>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ $equipment->equipmentType->name ?? ($equipment->category ?? 'Équipement') }}
                            </p>
                            
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-gray-900 text-sm">
                                    {{ number_format($equipment->price ?? $equipment->unit_price ?? 0, 0, ',', ' ') }} FCFA
                                </span>
                                <span class="text-xs font-semibold {{ ($equipment->quantity ?? 0) > 0 ? 'text-red-500' : 'text-gray-400' }}">
                                    Qté: {{ $equipment->quantity ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    Aucun équipement disponible dans le stock.
                </div>
            @endforelse
        </div>

        <!-- ========================================================= -->
        <!-- MODALE : Gestion des Types d'équipement -->
        <!-- ========================================================= -->
        <div x-data="{ 
            open: false, 
            selectedTypeId: '', 
            types: {{ json_encode($equipmentTypes) }},
            selectedType: null,
            isEditing: false,
            editName: '',
            
            updateSelection() {
                this.selectedType = this.types.find(t => t.id == this.selectedTypeId) || null;
                if(this.selectedType) {
                    this.editName = this.selectedType.name;
                }
                this.isEditing = false;
            }
        }" 
        x-show="open" 
        @open-modal-add-type.window="open = true" 
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;">

            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/40 transition-opacity" @click="open = false"></div>

                <div class="bg-white rounded-xl max-w-md w-full p-6 z-10 shadow-xl border border-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Gestion des Types d'Équipement</h2>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                    </div>

                    <!-- Formulaire d'ajout de Type -->
                    <form action="{{ route('equipment-types.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nouveau type</label>
                            <input type="text" name="name" required placeholder="Ex: Caméra, Microphone..." class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Image associée</label>
                            <input type="file" name="image" accept="image/*" required class="w-full text-xs text-gray-500 border border-gray-300 rounded-md p-1">
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 font-semibold text-sm transition">
                            Enregistrer le type
                        </button>
                    </form>

                    <hr class="my-5 border-gray-200">

                    <!-- Sélection / Modification / Suppression -->
                    <div class="space-y-3">
                        <label class="block text-xs font-medium text-gray-700">Consulter / Modifier un type</label>
                        <select x-model="selectedTypeId" @change="updateSelection()" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Sélectionner un type --</option>
                            <template x-for="type in types" :key="type.id">
                                <option :value="type.id" x-text="type.name"></option>
                            </template>
                        </select>

                        <template x-if="selectedType">
                            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 flex flex-col items-center gap-3">
                                <img :src="'/storage/' + selectedType.image" class="w-24 h-24 object-contain rounded border bg-white p-1">

                                <template x-if="!isEditing">
                                    <p class="font-bold text-sm text-gray-800" x-text="selectedType.name"></p>
                                </template>

                                <template x-if="isEditing">
                                    <form :action="'/equipment-types/' + selectedType.id" method="POST" enctype="multipart/form-data" class="w-full space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" x-model="editName" class="w-full border rounded p-1 text-sm">
                                        <input type="file" name="image" accept="image/*" class="w-full text-xs text-gray-500">
                                        <div class="flex justify-end gap-2 pt-1">
                                            <button type="submit" class="bg-green-600 text-white text-xs px-3 py-1 rounded hover:bg-green-700">Valider</button>
                                            <button type="button" @click="isEditing = false" class="bg-gray-400 text-white text-xs px-3 py-1 rounded hover:bg-gray-500">Annuler</button>
                                        </div>
                                    </form>
                                </template>

                                <template x-if="!isEditing">
                                    <div class="flex gap-2">
                                        <button @click="isEditing = true" class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold rounded hover:bg-amber-600">
                                            Modifier
                                        </button>

                                        <form :action="'/equipment-types/' + selectedType.id" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce type ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="mt-5 text-right">
                        <button @click="open = false" class="px-4 py-1.5 bg-gray-200 text-gray-700 rounded-md text-xs hover:bg-gray-300">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- SLIDE-OVER : Ajouter un Équipement (Identique à la capture) -->
        <!-- ========================================================= -->
        <div x-show="isSlideOverOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div class="absolute inset-0 bg-black/40 transition-opacity" @click="isSlideOverOpen = false"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between">
                    
                    <div>
                        <!-- En-tête bleu/violet du panneau Slide-Over -->
                        <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white">Ajouter un équipement</h2>
                            <button @click="isSlideOverOpen = false" class="text-white hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Formulaire identique à la capture d'écran sans le champ Image -->
                        <form id="add-equipment-form" action="{{ route('equipments.store') }}" method="POST" class="p-6 space-y-4">
                            @csrf
                            
                            <!-- Type * -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Type *</label>
                                <select name="equipment_type_id" required class="w-full border border-gray-300 rounded-md p-2.5 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Sélectionner un type --</option>
                                    @foreach($equipmentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Intitulé * -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Intitulé *</label>
                                <input type="text" name="name" required class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <!-- Prix (FCFA) * & Quantité * -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Prix (FCFA) *</label>
                                    <input type="number" name="price" value="0" min="0" required class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Quantité *</label>
                                    <input type="number" name="quantity" value="0" min="1" required class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <!-- Date d'entrée -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Date d'entrée</label>
                                <input type="date" name="entry_date" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <!-- Marque -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Marque</label>
                                <input type="text" name="brand" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <!-- Caractéristiques -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Caractéristiques</label>
                                <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- Pied de page avec boutons Annuler / Ajouter -->
                    <div class="p-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50">
                        <button type="button" @click="isSlideOverOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" form="add-equipment-form" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold shadow-sm transition">
                            Ajouter
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>