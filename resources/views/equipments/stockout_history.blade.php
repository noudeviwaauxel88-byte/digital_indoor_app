<x-app-layout>
    <div class="px-6 sm:px-10 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Historique des sorties de stock</h1>
                <p class="text-sm text-gray-500 mt-1">Consultez l'ensemble des mouvements de matériel attribués aux utilisateurs.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('equipments.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg shadow-sm hover:bg-gray-50 flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour aux équipements
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">{{ $outs->total() }} mouvement(s) enregistré(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Équipement(s) & N° de Série</th>
                            <th class="py-3 px-4">Bénéficiaire</th>
                            <th class="py-3 px-4">Destination / Projet</th>
                            <th class="py-3 px-4">Date de sortie</th>
                            <th class="py-3 px-4">Motif</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($outs as $out)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <!-- Équipements -->
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

                                <!-- Utilisateur -->
                                <td class="py-3.5 px-4">
                                    @if($out->user)
                                        @php
                                            $userName = $out->user->name ?? trim(($out->user->firstname ?? '') . ' ' . ($out->user->lastname ?? ''));
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                                {{ strtoupper(substr($userName, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">{{ $userName }}</p>
                                                <p class="text-xs text-gray-400">{{ $out->user->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Non spécifié</span>
                                    @endif
                                </td>

                                <!-- Projet / Destination -->
                                <td class="py-3.5 px-4 text-gray-700">
                                    {{ $out->project->name ?? $out->other_destination ?? '—' }}
                                </td>

                                <!-- Date de sortie -->
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    {{ $out->movement_date ? \Carbon\Carbon::parse($out->movement_date)->format('d/m/Y') : '—' }}
                                </td>

                                <!-- Motif -->
                                <td class="py-3.5 px-4 text-gray-500 max-w-xs truncate" title="{{ $out->reason }}">
                                    {{ $out->reason ?? '—' }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('equipments.stockout.return', $out) }}" onsubmit="return confirm('Confirmez-vous le retour de ces équipements en stock ?');" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 text-xs font-semibold rounded-md transition-colors flex items-center gap-1 ml-auto">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Remettre en stock
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <p class="text-gray-500 font-medium">Aucun mouvement de sortie enregistré.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($outs->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $outs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>