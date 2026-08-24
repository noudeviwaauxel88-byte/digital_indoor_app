<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4">
        <a href="{{ route('equipments.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-4">
            ← Retour à la liste du stock
        </a>

        <h1 class="text-2xl font-bold text-gray-800 mb-6">Sortie de Stock : {{ $equipment->title }}</h1>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4 pb-6 border-b border-gray-100 mb-6">
                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center font-bold text-gray-400">
                    {{ strtoupper(substr($equipment->title, 0, 2)) }}
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">{{ $equipment->title }}</h3>
                    <p class="text-sm text-gray-500">{{ $equipment->type }} {{ $equipment->brand ? '- ' . $equipment->brand : '' }}</p>
                    <p class="text-xs font-semibold text-indigo-600 mt-1">
                        Articles en stock : {{ $equipment->availableItems->count() }}
                    </p>
                </div>
            </div>

            <form action="{{ route('equipments.stockout.store', $equipment) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date de sortie *</label>
                        <input type="date" name="movement_date" value="{{ date('Y-m-d') }}" required class="w-full border-gray-300 rounded-lg text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Demandeur *</label>
                        <select name="user_id" required class="w-full border-gray-300 rounded-lg text-sm">
                            <option value="">Sélectionner un utilisateur</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Projet</label>
                        <select name="project_id" class="w-full border-gray-300 rounded-lg text-sm">
                            <option value="">Aucun projet</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Autre / Destination</label>
                        <input type="text" name="other_destination" placeholder="Ex: Maintenance, Perte..." class="w-full border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Articles à sortir (Numéros de Série) *</label>
                    @if($equipment->availableItems->isEmpty())
                        <p class="text-sm text-red-500 font-medium">Cet article est en rupture de stock.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-gray-50">
                            @foreach($equipment->availableItems as $item)
                                <label class="flex items-center gap-2 text-xs font-mono text-gray-700 bg-white p-2 rounded border border-gray-200 cursor-pointer hover:bg-indigo-50">
                                    <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" class="rounded text-indigo-600">
                                    <span>S/N: {{ $item->serial_number }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Motif / Détails supplémentaires</label>
                    <textarea name="reason" rows="3" class="w-full border-gray-300 rounded-lg text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Joindre un fichier (PDF, Word, Image)</label>
                    <input type="file" name="document" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('equipments.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" @if($equipment->availableItems->isEmpty()) disabled @endif class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-medium disabled:opacity-50 hover:bg-indigo-700">
                        Enregistrer la sortie
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>