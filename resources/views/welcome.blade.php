<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Indoor - Gestion d'entreprise</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-secondary">

    <div class="container mx-auto px-6">
        <nav class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-primary">DIGITAL INDOOR INC</h1>
            </div>

            <div class="flex items-center space-x-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-dark font-semibold hover:text-primary">Tableau de bord</a>
                    @else
                        <a href="{{ route('login') }}" class="text-dark font-semibold hover:text-primary">Connexion</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 px-5 py-2 bg-primary text-light font-semibold rounded-lg shadow-md hover:bg-opacity-90">Inscrivez-vous</a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>

        <div class="text-center py-24">
            <h1 class="text-4xl md:text-6xl font-extrabold text-dark leading-tight">
                Une plateforme pour
                <span class="text-primary">tout gérer</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg text-gray-600">
                Centralisez les projets, les tâches et la communication en un seul espace pour éviter le chaos et vous permettre de vous concentrer sur l'essentiel.
            </p>

            
        </div>

        <div class="flex justify-center items-center space-x-12 pb-24 text-gray-600">
            <div class="text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                <span class="font-semibold">Projets</span>
            </div>
            <div class="text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="font-semibold">Équipes</span>
            </div>
            <div class="text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="font-semibold">Calendrier</span>
            </div>
             <div class="text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-semibold">Tâches</span>
            </div>
        </div>

    </div>

</body>
</html>