<x-app-layout>
    <div x-data="{ isStockMovementModalOpen: false, selectedMovement: null }">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Historique des Sorties de Stock</h1>
            <a href="{{ route('equipments.index') }}" class="px-4 py-2 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 shadow-sm hover:bg-gray-50 text-sm">&larr; Retour au Stock</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse ($movements as $movement)
                {{-- Carte cliquable --}}
                <button @click="selectedMovement = {{ Js::from($movement) }}; isStockMovementModalOpen = true" class="w-full text-left bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-300">
                    
                    {{-- Image de l'équipement --}}
                    @if($item = $movement->equipmentItems->first())
                        <div class="bg-white p-4 h-48 flex items-center justify-center">
                            <img class="max-h-full max-w-full object-contain" src="{{ $item->equipment->image_path ? asset('storage/'. $item->equipment->image_path) : 'https://placehold.co/400x400/e2e8f0/e2e8f0?text=.' }}" alt="{{ $item->equipment->title }}">
                        </div>
                    @else
                        <div class="bg-gray-100 p-4 h-48 flex items-center justify-center text-gray-400 text-sm italic">Image indisponible</div>
                    @endif

                    <div class="p-4 border-t border-gray-100">
                        <h3 class="font-semibold text-gray-800 truncate">{{ $movement->equipmentItems->first()->equipment->title ?? 'Équipement Supprimé' }}</h3>
                        
                        {{-- Sous-titre (Nombre d'articles ou Type) --}}
                        @if($movement->equipmentItems->count() > 1)
                            <p class="text-sm text-gray-500 mt-1">(+ {{ $movement->equipmentItems->count() - 1 }} autre{{ $movement->equipmentItems->count() > 2 ? 's' : '' }} article{{ $movement->equipmentItems->count() > 2 ? 's' : '' }})</p>
                        @else
                            <p class="text-sm text-gray-500 mt-1">{{ $movement->equipmentItems->first()->equipment->type ?? 'N/A' }}</p>
                        @endif
                        
                        {{-- Badge Projet ou Destination --}}
                        @if($movement->project)
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 mt-2">{{ $movement->project->name }}</span>
                        @elseif($movement->other_destination)
                            <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 mt-2">{{ Str::limit($movement->other_destination, 15) }}</span>
                        @endif

                        <div class="mt-4 flex justify-between items-center">
                            <p class="text-lg font-bold text-red-600">- {{ $movement->equipmentItems->count() }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($movement->movement_date)->format('d/m/Y') }}</p>
                        </div>

                        {{-- Affichage du Nom sur la carte --}}
                        @if($movement->user)
                            <p class="text-xs text-gray-500 mt-2 font-medium">Par : {{ $movement->user->name }}</p>
                        @endif
                    </div>
                </button>
            @empty
                <div class="col-span-full text-center py-12"><p class="text-gray-500">Aucune sortie de stock enregistrée pour le moment.</p></div>
            @endforelse
        </div>

        {{-- MODAL DES DÉTAILS --}}
        <div x-show="isStockMovementModalOpen" @keydown.escape.window="isStockMovementModalOpen = false" x-cloak class="relative z-20">
            <div x-show="isStockMovementModalOpen" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="isStockMovementModalOpen" @click.away="isStockMovementModalOpen = false" class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <div class="flex justify-between items-center"><h3 class="text-base font-semibold leading-6 text-gray-900">Détails de la Sortie</h3><button @click="isStockMovementModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button></div>
                                <div class="mt-4">
                                    <dl class="divide-y divide-gray-100">
                                        {{-- Date --}}
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"><dt class="text-sm font-medium leading-6 text-gray-900">Date</dt><dd x-text="selectedMovement ? new Date(selectedMovement.movement_date).toLocaleDateString('fr-FR') : ''" class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"></dd></div>
                                        
                                        {{-- Demandeur (C'est ici que ça s'affiche) --}}
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Demandeur</dt>
                                            <dd x-text="selectedMovement && selectedMovement.user ? selectedMovement.user.name : 'Utilisateur inconnu'" class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"></dd>
                                        </div>
                                        
                                        {{-- Projet --}}
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0" x-show="selectedMovement && selectedMovement.project">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Projet</dt>
                                            <dd x-text="selectedMovement && selectedMovement.project ? selectedMovement.project.name : ''" class="mt-1 text-sm font-bold text-blue-600 sm:col-span-2 sm:mt-0"></dd>
                                        </div>

                                        {{-- Destination autre --}}
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0" x-show="selectedMovement && selectedMovement.other_destination">
                                            <dt class="text-sm font-medium leading-6 text-gray-900">Destination</dt>
                                            <dd x-text="selectedMovement ? selectedMovement.other_destination : ''" class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"></dd>
                                        </div>

                                        {{-- Motif --}}
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"><dt class="text-sm font-medium leading-6 text-gray-900">Motif</dt><dd x-text="selectedMovement && selectedMovement.reason ? selectedMovement.reason : 'Aucun'" class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"></dd></div>
                                        
                                        {{-- Fichier --}}
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0" x-show="selectedMovement && selectedMovement.file_path"><dt class="text-sm font-medium leading-6 text-gray-900">Fichier</dt><dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"><a :href="selectedMovement ? '/storage/' + selectedMovement.file_path : '#'" target="_blank" class="text-blue-600 hover:underline">Télécharger</a></dd></div>
                                        
                                        {{-- Liste des articles --}}
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"><dt class="text-sm font-medium leading-6 text-gray-900">Articles</dt><dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"><ul class="list-disc list-inside space-y-1"><template x-for="item in selectedMovement.equipment_items" :key="item.id"><li><span x-text="item.equipment.title" class="font-medium"></span> (<span x-text="item.serial_number"></span>)</li></template></ul></dd></div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        
                        {{-- PIED DE PAGE DU MODAL (AVEC BOUTON SUPPRIMER) --}}
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:justify-between sm:px-6">
                             <button @click="isStockMovementModalOpen = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:ml-3">Fermer</button>
                            
                             <form x-bind:action="'{{ url('equipments/stockout') }}/' + (selectedMovement ? selectedMovement.id : '')" method="POST" x-ref="deleteForm" x-show="selectedMovement">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        @click="if (confirm('ATTENTION : Êtes-vous sûr ?\n\nCela va annuler la sortie et REMETTRE les articles en stock.')) $refs.deleteForm.submit()"
                                        class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto">
                                    Supprimer la Sortie
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>