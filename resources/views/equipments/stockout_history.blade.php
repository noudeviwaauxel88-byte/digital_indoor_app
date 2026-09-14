<x-app-layout>
    {{-- Styles dédiés à l'impression --}}
    <style>
        @media print {
            /* Masquer les éléments de navigation, boutons et colonnes d'actions */
            nav, header, sidebar, .no-print {
                display: none !important;
            }

            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-size: 12pt;
                margin: 0;
                padding: 15mm;
            }

            /* Réinitialiser les ombres et conteneurs pour le papier */
            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 8px !important;
            }

            thead tr {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-break {
                page-break-inside: avoid;
            }
        }
    </style>

    <div class="px-6 sm:px-10 py-8 print-container">
        {{-- En-tête de page (Masqué à l'impression pour le bloc d'action) --}}
        <div class="flex justify-between items-center mb-6 no-print">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Historique des sorties de stock</h1>
                <p class="text-sm text-gray-500 mt-1">Consultez l'ensemble des mouvements de matériel attribués aux utilisateurs.</p>
            </div>
            
            <div class="flex items-center gap-3">
                {{-- Bouton Imprimer / Export --}}
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm text-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Imprimer / Exporter PDF
                </button>

                <a href="{{ route('equipments.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg shadow-sm hover:bg-gray-50 text-sm">
                    ← Retour aux équipements
                </a>
            </div>
        </div>

        {{-- Titre visible UNIQUEMENT à l'impression --}}
        <div class="hidden print:block mb-6">
            <h1 class="text-xl font-bold text-gray-900">Rapport d'Historique des Sorties de Stock</h1>
            <p class="text-xs text-gray-500">Généré le {{ date('d/m/Y à H:i') }}</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg no-print">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                            <th class="py-3 px-4">Équipement(s) & N° de Série</th>
                            <th class="py-3 px-4">Bénéficiaire</th>
                            <th class="py-3 px-4">Destination / Projet</th>
                            <th class="py-3 px-4">Date de sortie</th>
                            <th class="py-3 px-4">Motif</th>
                            <th class="py-3 px-4 no-print">Document</th>
                            <th class="py-3 px-4 text-right no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($outs as $out)
                            <tr class="hover:bg-gray-50/80 page-break">
                                <td class="py-3.5 px-4">
                                    <ul class="space-y-1">
                                        @foreach($out->equipmentItems as $item)
                                            <li>
                                                <span class="font-medium text-gray-900">{{ $item->equipment->title ?? 'N/A' }}</span>
                                                <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">S/N: {{ $item->serial_number }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($out->user)
                                        @php $userName = $out->user->name ?? trim(($out->user->firstname ?? '') . ' ' . ($out->user->lastname ?? '')); @endphp
                                        <p class="font-medium text-gray-800">{{ $userName }}</p>
                                        <p class="text-xs text-gray-400">{{ $out->user->email }}</p>
                                    @else
                                        <span class="text-gray-400 italic">Non spécifié</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-gray-700">
                                    {{ $out->project->name ?? $out->other_destination ?? '—' }}
                                </td>

                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    {{ $out->movement_date ? \Carbon\Carbon::parse($out->movement_date)->format('d/m/Y') : '—' }}
                                </td>

                                <td class="py-3.5 px-4 text-gray-500 max-w-xs truncate" title="{{ $out->reason }}">
                                    {{ $out->reason ?? '—' }}
                                </td>

                                <td class="py-3.5 px-4 no-print">
                                    @if($out->file_path)
                                        <a href="{{ Storage::url($out->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline">
                                            📎 Voir le fichier
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Aucun</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap no-print">
                                    <form method="POST" action="{{ route('equipments.stockout.return', $out) }}" onsubmit="return confirm('Voulez-vous annuler cette sortie et remettre les articles en stock ?');" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 text-xs font-semibold rounded-md transition-colors">
                                            Annuler la sortie
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-gray-500">
                                    Aucun mouvement de sortie enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($outs->hasPages())
                <div class="p-4 border-t border-gray-100 no-print">
                    {{ $outs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>