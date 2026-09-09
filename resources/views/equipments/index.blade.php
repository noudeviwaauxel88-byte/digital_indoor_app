<x-app-layout>
    <div class="py-6 px-4 sm:px-6 lg:px-8" x-data="{ isSlideOverOpen: false }">
        
        <!-- En-tête de la page -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Gestion du Stock</h1>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('equipments.stockout.history') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 text-sm">
                    Historique Sorties
                </a>

                <!-- Bouton d'ouverture de la modale des Types -->
                <button @click="$dispatch('open-modal-add-type')" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 text-sm">
                    + Type Équipement
                </button>
                
                <!-- Formulaire de recherche -->
                <form action="{{ route('equipments.index') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" name="search" placeholder="Rechercher..." value="{{ request('search') }}" class="px-4 py-2 pl-10 border rounded-lg w-48 sm:w-64 dark:bg-gray-700 dark:text-white dark:border-gray-600 text-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </form>

                <!-- Bouton Ajouter Équipement -->
                <button @click="isSlideOverOpen = true" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 text-sm">
                    + Nouveau
                </button>
            </div>
        </div>

        <!-- Grille des équipements -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($equipments as $equipment)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 flex flex-col items-center shadow-sm hover:shadow-md transition">
                    
                    <!-- Image liée au TYPE d'équipement -->
                    <div class="w-full h-40 flex items-center justify-center mb-3 bg-gray-50 dark:bg-gray-900 rounded-lg p-2">
                        @if($equipment->equipmentType && $equipment->equipmentType->image)
                            <img src="{{ asset('storage/' . $equipment->equipmentType->image) }}" 
                                 alt="{{ $equipment->equipmentType->name }}" 
                                 class="max-h-full max-w-full object-contain">
                        @else
                            <span class="text-gray-400 text-xs">Aucune image disponible</span>
                        @endif
                    </div>

                    <!-- Informations Équipement -->
                    <h3 class="font-bold text-gray-800 dark:text-white text-center text-base mb-1">{{ $equipment->name }}</h3>
                    <span class="inline-block px-2.5 py-0.5 text-xs font-medium text-indigo-800 bg-indigo-100 rounded-full dark:bg-indigo-900 dark:text-indigo-200 mb-2">
                        {{ $equipment->equipmentType->name ?? 'Non catégorisé' }}
                    </span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Réf: {{ $equipment->reference ?? 'N/A' }}</p>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                    Aucun équipement enregistré pour le moment.
                </div>
            @endforelse
        </div>

        <!-- ========================================================= -->
        <!-- MODALE : Gestion des Types d'équipement (Ajout / Liste / Modif / Suppr) -->
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
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="open = false"></div>

                <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 z-10 shadow-2xl border border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Gestion des Types d'Équipement</h2>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
                    </div>

                    <!-- Formulaire d'ajout de Type -->
                    <form action="{{ route('equipment-types.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nouveau type</label>
                            <input type="text" name="name" required placeholder="Ex: Caméra, Microphone..." class="mt-1 w-full border border-gray-300 dark:border-gray-600 rounded-md p-2 dark:bg-gray-700 dark:text-white text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image associée</label>
                            <input type="file" name="image" accept="image/*" required class="mt-1 w-full text-xs text-gray-500 border border-gray-300 dark:border-gray-600 rounded-md p-1 dark:bg-gray-700">
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 font-semibold text-sm transition">
                            Enregistrer le type
                        </button>
                    </form>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <!-- Liste déroulante des types existants -->
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Consulter / Modifier un type</label>
                        <select x-model="selectedTypeId" @change="updateSelection()" class="w-full border border-gray-300 dark:border-gray-600 rounded-md p-2 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">-- Sélectionner un type --</option>
                            <template x-for="type in types" :key="type.id">
                                <option :value="type.id" x-text="type.name"></option>
                            </template>
                        </select>

                        <!-- Panneau de détails du type sélectionné -->
                        <template x-if="selectedType">
                            <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 flex flex-col items-center gap-3">
                                
                                <img :src="'/storage/' + selectedType.image" class="w-28 h-28 object-contain rounded-md border bg-white p-1">

                                <template x-if="!isEditing">
                                    <p class="font-bold text-base text-gray-800 dark:text-white" x-text="selectedType.name"></p>
                                </template>

                                <!-- Formulaire d'édition du type -->
                                <template x-if="isEditing">
                                    <form :action="'/equipment-types/' + selectedType.id" method="POST" enctype="multipart/form-data" class="w-full space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" x-model="editName" class="w-full border rounded p-1 text-sm dark:bg-gray-800 dark:text-white">
                                        <input type="file" name="image" accept="image/*" class="w-full text-xs text-gray-500">
                                        <div class="flex justify-end gap-2 pt-1">
                                            <button type="submit" class="bg-green-600 text-white text-xs px-3 py-1 rounded hover:bg-green-700">Valider</button>
                                            <button type="button" @click="isEditing = false" class="bg-gray-400 text-white text-xs px-3 py-1 rounded hover:bg-gray-500">Annuler</button>
                                        </div>
                                    </form>
                                </template>

                                <!-- Actions : Modifier / Supprimer -->
                                <template x-if="!isEditing">
                                    <div class="flex gap-2">
                                        <button @click="isEditing = true" class="px-3 py-1 bg-yellow-500 text-white text-xs font-semibold rounded hover:bg-yellow-600">
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

                    <div class="mt-6 text-right">
                        <button @click="open = false" class="px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-xs hover:bg-gray-300">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- SLIDE-OVER : Ajouter un Équipement (Sans champ Image) -->
        <!-- ========================================================= -->
        <div x-show="isSlideOverOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div class="absolute inset-0 bg-black/50" @click="isSlideOverOpen = false"></div>
            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-white dark:bg-gray-800 p-6 shadow-xl border-l dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Ajouter un Équipement</h2>
                    
                    <form action="{{ route('equipments.store') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de l'équipement</label>
                            <input type="text" name="name" required class="mt-1 w-full border rounded-md p-2 dark:bg-gray-700 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Référence / Modèle</label>
                            <input type="text" name="reference" class="mt-1 w-full border rounded-md p-2 dark:bg-gray-700 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type d'Équipement</label>
                            <select name="equipment_type_id" required class="mt-1 w-full border rounded-md p-2 dark:bg-gray-700 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                                <option value="">-- Sélectionner un type --</option>
                                @foreach($equipmentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité en stock</label>
                            <input type="number" name="quantity" min="1" value="1" required class="mt-1 w-full border rounded-md p-2 dark:bg-gray-700 dark:text-white border-gray-300 dark:border-gray-600 text-sm">
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="isSlideOverOpen = false" class="px-4 py-2 border rounded-md text-sm text-gray-600 dark:text-gray-300">Annuler</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700">Créer l'équipement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>