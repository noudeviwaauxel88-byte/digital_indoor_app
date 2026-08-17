<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-light">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8">
            
            {{-- ========================================================= --}}
            {{-- == AFFICHE LE MESSAGE DE SUCCÈS APRÈS L'INSCRIPTION == --}}
            {{-- ========================================================= --}}
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Titres centrés -->
            <div class="text-center mb-10">
                <a href="/">
                    <x-application-logo class="w-20 h-20 mx-auto fill-current text-gray-500" />
                </a>
                <h1 class="text-3xl font-bold text-dark mt-4">Se connecter</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Entrez dans votre espace de travail et rejoignez vos coéquipiers.
                </p>
            </div>

            <!-- Formulaire de connexion -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Adresse Email -->
                <div>
                    <x-input-label for="email" value="E-mail" class="font-semibold"/>
                    <input id="email" class="block mt-1 w-full bg-secondary border-gray-200 rounded-md shadow-sm" type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Mot de passe -->
                <div class="mt-6">
                    <x-input-label for="password" value="Mot de passe" class="font-semibold"/>
                    <input id="password" class="block mt-1 w-full bg-secondary border-gray-200 rounded-md shadow-sm"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Mot de passe oublié -->
                <div class="text-right mt-2">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm font-semibold text-primary hover:text-opacity-80" href="{{ route('password.request') }}">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <!-- Bouton de connexion principal -->
                <div class="mt-6">
                    <button type="submit" class="w-full justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-light bg-primary hover:bg-opacity-90">
                        Se connecter
                    </button>
                </div>
            </form>

            <!-- Lien pour s'inscrire -->
            <p class="mt-8 text-center text-sm text-gray-600">
                Nouveau sur notre plateforme ?
                <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-opacity-80">
                    Créer un compte
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
