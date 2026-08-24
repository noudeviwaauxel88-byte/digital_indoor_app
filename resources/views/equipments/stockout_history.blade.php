<x-app-layout>
    <div class="px-6 sm:px-10 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Historique des sorties de stock</h1>
                <p class="text-sm text-gray-500 mt-1">Consultez l'ensemble des mouvements de matériel attribués aux utilisateurs.</p>
            </div>
            
            <a href="{{ route('equipments.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg shadow-sm hover:bg-gray-50 text-sm">
                ← Retour aux équipements
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
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
                            <th class="py-3 px-4">Document</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($outs as $out)
                            <tr class="hover:bg-gray-50/80">
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

                                <td class="py-3.5 px-4">
                                    @if($out->file_path)
                                        <a href="{{ Storage::url($out->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline">
                                            📎 Voir le fichier
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Aucun</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
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
                <div class="p-4 border-t border-gray-100">
                    {{ $outs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>