<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S'inscrire - Digital Indoor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <a href="/" class="absolute top-0 left-0 p-6 z-10"><h1 class="text-xl font-bold text-primary">DIGITAL INDOOR INC</h1></a>
    <div class="min-h-screen flex">
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div>
                    <h2 class="mt-6 text-3xl font-extrabold text-gray-900">S'inscrire</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Préparez votre compte pour rejoindre vos collègues !
                    </p>
                </div>
                <div class="mt-8">
                    <form action="{{ route('register') }}" method="POST" class="space-y-6">
                        @csrf
                        {{-- Formulaire corrigé avec Prénom et Nom --}}
                        <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
                            <div>
                                <label for="firstname" class="block text-sm font-medium text-gray-700">Prénom</label>
                                <div class="mt-1">
                                    <input id="firstname" name="firstname" type="text" value="{{ old('firstname') }}" required autofocus class="w-full bg-gray-100 border-transparent rounded-md focus:border-gray-500 focus:bg-white focus:ring-0">
                                </div>
                                <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
                            </div>
                            <div>
                                <label for="lastname" class="block text-sm font-medium text-gray-700">Nom</label>
                                <div class="mt-1">
                                    <input id="lastname" name="lastname" type="text" value="{{ old('lastname') }}" required class="w-full bg-gray-100 border-transparent rounded-md focus:border-gray-500 focus:bg-white focus:ring-0">
                                </div>
                                <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                            <div class="mt-1">
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full bg-gray-100 border-transparent rounded-md focus:border-gray-500 focus:bg-white focus:ring-0">
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                            <div class="mt-1">
                                <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full bg-gray-100 border-transparent rounded-md focus:border-gray-500 focus:bg-white focus:ring-0">
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmez le mot de passe</label>
                            <div class="mt-1">
                                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full bg-gray-100 border-transparent rounded-md focus:border-gray-500 focus:bg-white focus:ring-0">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-opacity-90">
                                S'inscrire
                            </button>
                        </div>
                    </form>

                    <p class="mt-8 text-center text-sm text-gray-600">
                        Vous avez déjà un compte ?
                        <a href="{{ route('login') }}" class="font-medium text-primary hover:text-opacity-80">
                            Se connecter
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <div class="hidden lg:block relative w-0 flex-1 bg-primary text-white p-12">
           <div class="flex flex-col h-full">
               <h2 class="text-3xl font-bold">Maximisez l'efficacité, l'unité et le potentiel avec Digital Indoor.</h2>
           </div>
        </div>
    </div>
</body>
</html>

