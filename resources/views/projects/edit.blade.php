<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Modifier le projet : {{ $project->name }}</h1>

        <div class="bg-white shadow-sm rounded-lg p-6 sm:p-8">
            {{-- Le formulaire envoie les données à la route 'projects.update' --}}
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf
                @method('PATCH') {{-- Important: Spécifie que c'est une requête de mise à jour --}}

                <div class="space-y-6">
                    <!-- Champ: Nom -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom du projet *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        {{-- Affiche les erreurs de validation pour le champ 'name' --}}
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Champ: Structure -->
                    <div>
                        <label for="structure" class="block text-sm font-medium text-gray-700">Structure</label>
                        <select id="structure" name="structure" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Sélectionner une structure</option>
                            @foreach(['DHI', 'DIINC', 'HACCO', 'DIM'] as $structure)
                                {{-- Sélectionne l'option qui correspond à la valeur actuelle du projet --}}
                                <option value="{{ $structure }}" @selected(old('structure', $project->structure) == $structure)>
                                    {{ $structure }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('structure')" class="mt-2" />
                    </div>
                    
                    <!-- Champ: Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">{{ old('description', 'Bureautique') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-4">
                    <a href="{{ route('projects.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
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

