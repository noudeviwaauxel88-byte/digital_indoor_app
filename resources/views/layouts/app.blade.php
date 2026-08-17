<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Digital Indoor') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts and Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-gray-100">
            <!-- Sidebar -->
            <aside class="w-64 flex-shrink-0 bg-white border-r flex flex-col fixed h-full z-30">
                <a href="{{ route('profile.edit') }}" class="block p-4 border-b hover:bg-gray-50">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg">
                            {{-- Affiche la première lettre du prénom --}}
                            {{ strtoupper(substr(Auth::user()->firstname ?? Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="ml-3 overflow-hidden">
                            <p class="font-semibold text-gray-800 truncate">{{ Auth::user()->firstname ?? Auth::user()->name }}</p>
                            
                            {{-- AJOUT : Affichage du Rôle sous le nom --}}
                            <p class="text-[10px] uppercase font-bold text-purple-600 truncate">
                                {{ Auth::user()->getRoleNames()->first() ?? 'Digital Indoor Inc.' }}
                            </p>
                        </div>
                    </div>
                </a>

                <!-- Main Navigation -->
                <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span>Tableau de Bord</span>
                    </a>
                    
                    <a href="{{ route('projects.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('projects.*') || request()->routeIs('tasks.*') ? 'bg-primary text-white' : 'hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        <span>Projets</span>
                    </a>
                    
                    <a href="{{ route('equipments.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('equipments.*') ? 'bg-primary text-white' : 'hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        <span>Stock</span>
                    </a>
                    
                    <a href="{{ route('calendar.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('calendar.*') ? 'bg-primary text-white' : 'hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Calendrier</span>
                    </a>
                    
                    <a href="{{ route('files.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('files.*') ? 'bg-primary text-white' : 'hover:bg-gray-200' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        <span>Fichiers</span>
                    </a>

                    {{-- ================================================== --}}
                    {{-- == MENU ADMIN : Visible seulement pour SuperAdmin == --}}
                    {{-- ================================================== --}}
                    @role('SuperAdmin')
                    <div class="pt-4 mt-4 border-t border-gray-100">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Administration</p>
                        <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg {{ request()->routeIs('users.*') ? 'bg-purple-50 text-purple-700' : 'hover:bg-purple-50 hover:text-purple-700' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span>Utilisateurs</span>
                        </a>
                        <a href="{{ route('activity_logs.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <span>Historique (Logs)</span>
</a>
                    </div>
                    @endrole

                </nav>
                
                 <div class="p-4 border-t">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span>Déconnexion</span>
                        </a>
                    </form>
                </div>
            </aside>
            
            <!-- Main Content -->
            <div class="flex-1 flex flex-col ml-64">
                <main class="flex-grow p-6 sm:p-10">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>