<x-app-layout>
    <div class="px-6 sm:px-10 py-8">
        <!-- Header -->
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

        <!-- Notification de succès -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Tableau des sorties -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">{{ $outs->total() }} mouvement(s) enregistré(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Équipement</th>
                            <th class="py-3 px-4">Bénéficiaire</th>
                            <th class="py-3 px-4">Date de sortie</th>
                            <th class="py-3 px-4">Date de retour prévue</th>
                            <th class="py-3 px-4">Statut</th>
                            <th class="py-3 px-4">Notes</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($outs as $out)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <!-- Équipement & Code Exemplaire -->
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $out->item->equipment->title ?? 'Équipement supprimé' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Marque: {{ $out->item->equipment->brand ?? 'N/A' }} 
                                        @if(isset($out->item->code))
                                            • <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">#{{ $out->item->code }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Utilisateur -->
                                <td class="py-3.5 px-4">
                                    @if($out->user)
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                                {{ strtoupper(substr($out->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">{{ $out->user->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $out->user->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Non spécifié</span>
                                    @endif
                                </td>

                                <!-- Date de sortie -->
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($out->movement_date ?? $out->created_at)->format('d/m/Y') }}
                                </td>

                                <!-- Date de retour prévue -->
                                <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                    @if($out->return_date)
                                        {{ \Carbon\Carbon::parse($out->return_date)->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <!-- Statut -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($out->item && $out->item->status === 'returned')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Retourné
                                        </span>
                                    @elseif($out->return_date && \Carbon\Carbon::parse($out->return_date)->isPast())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            En retard
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            En cours
                                        </span>
                                    @endif
                                </td>

                                <!-- Notes -->
                                <td class="py-3.5 px-4 text-gray-500 max-w-xs truncate" title="{{ $out->reason }}">
                                    {{ $out->reason ?? '—' }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @hasanyrole('SuperAdmin|Manager')
                                        @if($out->item && $out->item->status !== 'returned')
                                            <form method="POST" action="{{ route('equipments.stockout.return', $out) }}" onsubmit="return confirm('Confirmez-vous le retour de cet équipement ?');" class="inline-block">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 text-xs font-semibold rounded-md transition-colors flex items-center gap-1 ml-auto">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Marquer retourné
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Aucune action</span>
                                        @endif
                                    @endhasanyrole
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <div class="flex flex-col items-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3 text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 font-medium">Aucun mouvement de sortie enregistré.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($outs->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $outs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>