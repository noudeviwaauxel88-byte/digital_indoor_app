<x-app-layout>
    {{-- Styles dédiés à l'impression --}}
    <style>
        @media print {
            nav, header, sidebar, .no-print, [x-show="isSlideOverOpen"], [x-show="open"], .screen-grid {
                display: none !important;
            }

            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 10pt;
                margin: 0;
                padding: 5mm;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            .print-only-table {
                display: block !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 6px 8px !important;
                text-align: left;
            }

            th {
                background-color: #f8fafc !important;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 8pt;
            }

            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>

    <div class="py-6 px-4 sm:px-6 lg:px-8 print-container" x-data="{ 
        isSlideOverOpen: false,
        selectedEquipment: null,
        isInfoModalOpen: false
    }">
        
        <!-- En-tête de la page (Visible uniquement à l'écran) -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 no-print">
            <h1 class="text-2xl font-bold text-gray-800">Gestion du Stock</h1>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Bouton Imprimer / PDF -->
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm text-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Imprimer / PDF
                </button>

                <!-- Bouton Historique Sorties -->
                <a href="{{ route('equipments.stockout.history') }}" class="px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 shadow-sm hover:bg-gray-50 text-sm">
                    Historique Sorties
                </a>
                
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

        <!-- SECTION D'IMPRESSION -->
        <div class="hidden print-only-table mb-6">
            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900">État Général du Stock d'Équipements</h1>
                <p class="text-xs text-gray-500">Généré le {{ date('d/m/Y à H:i') }}</p>
            </div>

            @php
                $totalQuantity = 0;
                $totalStockCost = 0;
            @endphp

            <table class="w-full text-xs">
                <thead>
                    <tr>
                        <th class="w-12 text-center">N°</th>
                        <th>Équipement</th>
                        <th>Type</th>
                        <th>Marque</th>
                        <th class="text-right">Prix Unitaire</th>
                        <th class="text-center">Qté en Stock</th>
                        <th class="text-right">Valeur Totale</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipments as $index => $equipment)
                        @php
                            $qty = $equipment->available_items_count ?? $equipment->items_count ?? $equipment->items->where('status', 'en_stock')->count();
                            $price = $equipment->price ?? $equipment->unit_price ?? 0;
                            $lineTotal = $price * $qty;
                            $typeName = $equipment->equipmentType->name ?? $equipment->type ?? 'Non spécifié';

                            $totalQuantity += $qty;
                            $totalStockCost += $lineTotal;
                        @endphp
                        <tr>
                            <td class="text-center font-mono">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $equipment->title ?? $equipment->name }}</td>
                            <td>{{ $typeName }}</td>
                            <td>{{ $equipment->brand ?? '—' }}</td>
                            <td class="text-right">{{ number_format($price, 0, ',', ' ') }} FCFA</td>
                            <td class="text-center font-bold">{{ $qty }}</td>
                            <td class="text-right font-bold">{{ number_format($lineTotal, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-gray-500">Aucun équipement disponible dans le stock.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <td colspan="5" class="text-right uppercase px-3 py-2">Totaux Généraux :</td>
                        <td class="text-center px-3 py-2 font-black text-sm">{{ $totalQuantity }}</td>
                        <td class="text-right px-3 py-2 font-black text-sm text-indigo-900">{{ number_format($totalStockCost, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- GRILLE DE CARTES (Écran) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 screen-grid">
            @forelse($equipments as $equipment)
                @php
                    $qty = $equipment->available_items_count ?? $equipment->items_count ?? $equipment->items->where('status', 'en_stock')->count();
                    $typeObj = $equipment->equipmentType;
                    $typeName = $typeObj->name ?? $equipment->type ?? 'Autre';

                    // Récupération automatique du fichier PNG depuis public/images/equipment-types/
                    $slugName = \Illuminate\Support\Str::slug($typeName); 
                    $localImagePath = public_path("images/equipment-types/{$slugName}.png");

                    if (!empty($typeObj->image) && str_starts_with($typeObj->image, 'http')) {
                        $imageUrl = $typeObj->image;
                    } elseif (file_exists($localImagePath)) {
                        $imageUrl = asset("images/equipment-types/{$slugName}.png");
                    } else {
                        $imageUrl = asset("images/equipment-types/autre.png");
                    }
                @endphp

                <div @click="selectedEquipment = {{ json_encode([
                        'title' => $equipment->title ?? $equipment->name,
                        'type' => $typeName,
                        'price' => $equipment->price ?? $equipment->unit_price ?? 0,
                        'brand' => $equipment->brand,
                        'features' => $equipment->features ?? $equipment->description,
                        'entry_date' => $equipment->entry_date,
                        'quantity' => $qty,
                        'image' => $imageUrl,
                        'items' => $equipment->items
                    ]) }}; isInfoModalOpen = true" 
                     class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between relative group hover:shadow-md transition cursor-pointer">
                    
                    <div>
                        <!-- Menu Actions -->
                        <div class="absolute top-3 right-3 z-10 no-print" x-data="{ openMenu: false }">
                            <button @click.stop="openMenu = !openMenu" @click.away="openMenu = false" class="p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>

                            <div x-show="openMenu" x-transition class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-100 py-1 text-xs z-20" style="display: none;">
                                <a href="{{ route('equipments.stockout.create', $equipment) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50" @click.stop>Sortie de Stock</a>
                                <a href="{{ route('equipments.edit', $equipment) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50" @click.stop>Modifier</a>
                                <form action="{{ route('equipments.destroy', $equipment) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" @click.stop>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-50">Supprimer</button>
                                </form>
                            </div>
                        </div>

                        <!-- Image de l'équipement -->
                        <div class="w-full h-44 bg-gray-50 flex items-center justify-center p-4">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $equipment->title ?? $equipment->name }}" 
                                 class="max-h-full max-w-full object-contain"
                                 onerror="this.src='/images/equipment-types/autre.png';">
                        </div>

                        <!-- Info carte -->
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 text-sm mb-0.5 truncate" title="{{ $equipment->title ?? $equipment->name }}">
                                {{ $equipment->title ?? $equipment->name }}
                            </h3>
                            <p class="text-xs text-gray-500 mb-3">
                                Type: <span class="font-semibold text-gray-700">{{ $typeName }}</span>
                            </p>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-gray-900 text-sm">
                                    {{ number_format($equipment->price ?? $equipment->unit_price ?? 0, 0, ',', ' ') }} FCFA
                                </span>
                                <span class="text-xs font-semibold {{ $qty <= 3 ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                    Qté: {{ $qty }}
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

        <!-- MODALE DETAILS -->
        <div x-show="isInfoModalOpen" class="fixed inset-0 z-50 overflow-y-auto no-print" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/40 transition-opacity" @click="isInfoModalOpen = false"></div>

                <div class="bg-white rounded-xl max-w-lg w-full p-6 z-10 shadow-xl border border-gray-100 relative">
                    <button @click="isInfoModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                    
                    <template x-if="selectedEquipment">
                        <div>
                            <div class="flex items-center gap-4 mb-4 pb-4 border-b">
                                <div class="w-20 h-20 bg-gray-50 rounded-lg p-2 flex items-center justify-center border">
                                    <img :src="selectedEquipment.image" class="max-h-full max-w-full object-contain" onerror="this.src='/images/equipment-types/autre.png';">
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900" x-text="selectedEquipment.title"></h2>
                                    <p class="text-xs text-indigo-600 font-semibold" x-text="'Type : ' + selectedEquipment.type"></p>
                                    <p class="text-sm font-bold text-gray-800 mt-1" x-text="Number(selectedEquipment.price).toLocaleString('fr-FR') + ' FCFA'"></p>
                                </div>
                            </div>

                            <div class="space-y-3 text-xs text-gray-600">
                                <div class="grid grid-cols-2 gap-2">
                                    <div><span class="font-bold text-gray-700">Marque :</span> <span x-text="selectedEquipment.brand || 'N/A'"></span></div>
                                    <div><span class="font-bold text-gray-700">Quantité en stock :</span> <span :class="selectedEquipment.quantity <= 3 ? 'text-red-600 font-bold' : ''" x-text="selectedEquipment.quantity"></span></div>
                                    <div class="col-span-2"><span class="font-bold text-gray-700">Date d'entrée :</span> <span x-text="selectedEquipment.entry_date || 'N/A'"></span></div>
                                </div>

                                <div>
                                    <span class="font-bold text-gray-700">Caractéristiques :</span>
                                    <p class="mt-1 p-2 bg-gray-50 rounded border text-gray-600" x-text="selectedEquipment.features || 'Aucune description'"></p>
                                </div>

                                <div>
                                    <span class="font-bold text-gray-700">Numéros de série enregistrés :</span>
                                    <div class="mt-1 max-h-32 overflow-y-auto space-y-1 pr-1">
                                        <template x-if="selectedEquipment.items && selectedEquipment.items.length > 0">
                                            <template x-for="item in selectedEquipment.items" :key="item.id">
                                                <div class="flex justify-between items-center bg-gray-100 px-2 py-1 rounded text-xs">
                                                    <span class="font-mono" x-text="item.serial_number"></span>
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold" :class="item.status === 'en_stock' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" x-text="item.status"></span>
                                                </div>
                                            </template>
                                        </template>
                                        <template x-if="!selectedEquipment.items || selectedEquipment.items.length === 0">
                                            <p class="text-gray-400 italic">Aucun numéro de série disponible.</p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 text-right">
                                <button @click="isInfoModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-xs hover:bg-gray-300 font-semibold">
                                    Fermer
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- SLIDE-OVER : Ajouter un Équipement -->
        <div x-show="isSlideOverOpen" class="fixed inset-0 z-50 overflow-hidden no-print" style="display: none;">
            <div class="absolute inset-0 bg-black/40 transition-opacity" @click="isSlideOverOpen = false"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between" x-data="{ 
                    quantity: 1, 
                    serials: [''],
                    selectedTypeSlug: '',
                    typesMap: {{ json_encode($equipmentTypes->pluck('name', 'id')) }},
                    
                    updatePreview(event) {
                        let selectedId = event.target.value;
                        let typeName = this.typesMap[selectedId] || '';
                        if (typeName) {
                            // Génère le slug Alpine JS pour aperçu immédiat
                            this.selectedTypeSlug = typeName.toLowerCase()
                                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/(^-|-$)+/g, '');
                        } else {
                            this.selectedTypeSlug = '';
                        }
                    },
                    
                    updateSerials() {
                        let q = parseInt(this.quantity) || 0;
                        if (q < 1) q = 1;
                        while (this.serials.length < q) this.serials.push('');
                        while (this.serials.length > q) this.serials.pop();
                    }
                }">
                    
                    <div>
                        <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white">Ajouter un équipement</h2>
                            <button @click="isSlideOverOpen = false" class="text-white hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form id="add-equipment-form" action="{{ route('equipments.store') }}" method="POST" class="p-6 space-y-4 max-h-[calc(100vh-130px)] overflow-y-auto">
                            @csrf
                            
                            <!-- Champ Type avec Aperçu dynamique de l'image -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Type *</label>
                                <select name="equipment_type_id" @change="updatePreview($event)" required class="w-full border border-gray-300 rounded-md p-2.5 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Sélectionner un type --</option>
                                    @foreach($equipmentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                <!-- Aperçu dynamique du fichier local -->
                                <template x-if="selectedTypeSlug">
                                    <div class="mt-2 p-2 border rounded-lg bg-gray-50 flex items-center gap-3">
                                        <img :src="'/images/equipment-types/' + selectedTypeSlug + '.png'" 
                                             class="w-12 h-12 object-contain"
                                             onerror="this.src='/images/equipment-types/autre.png';">
                                        <span class="text-xs text-gray-500">Image associée détectée</span>
                                    </div>
                                </template>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Intitulé *</label>
                                <input type="text" name="title" required class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Prix (FCFA) *</label>
                                    <input type="number" name="price" value="0" min="0" required class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Quantité *</label>
                                    <input type="number" name="quantity" min="1" x-model="quantity" @input="updateSerials()" required class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="space-y-2 border-t pt-3">
                                <label class="block text-xs font-semibold text-gray-700">Numéro(s) de série *</label>
                                <template x-for="(serial, index) in serials" :key="index">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400 font-mono" x-text="'#' + (index + 1)"></span>
                                        <input type="text" :name="'serial_numbers[' + index + ']'" x-model="serials[index]" placeholder="Saisir le N° de série" required class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </template>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Date d'entrée</label>
                                <input type="date" name="entry_date" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Marque</label>
                                <input type="text" name="brand" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Caractéristiques</label>
                                <textarea name="features" rows="3" class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                        </form>
                    </div>

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