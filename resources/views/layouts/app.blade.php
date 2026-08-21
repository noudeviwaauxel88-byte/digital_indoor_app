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

        <!-- Scripts et Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen flex flex-col md:flex-row">

            <!-- En-tête mobile (Visible uniquement sur mobile/tablette) -->
            <div class="md:hidden bg-white border-b px-4 py-3 flex items-center justify-between sticky top-0 z-40">
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="font-bold text-gray-800 text-lg">Digital Indoor</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
                    {{ strtoupper(substr(Auth::user()->firstname ?? Auth::user()->name, 0, 1)) }}
                </div>
            </div>

            <!-- Overlay sombre en arrière-plan sur mobile quand le menu est ouvert -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false" 
                 class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" 
                 style="display: none;"></div>

            <!-- Sidebar (Mobile & Desktop) -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
                   class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r flex flex-col transform md:transform-none md:static md:translate-x-0 transition-transform duration-200 ease-in-out">
                
                <!-- Profil Utilisateur dans le menu -->
                <a href="{{ route('profile.edit') }}" class="block p-4 border-b hover:bg-gray-50">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg">
                            {{ strtoupper(substr(Auth::user()->firstname ?? Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="ml-3 overflow-hidden">
                            <p class="font-semibold text-gray-800 truncate">{{ Auth::user()->firstname ?? Auth::user()->name }}</p>
                            <p class="text-[10px] uppercase font-bold text-purple-600 truncate">
                                {{ Auth::user()->getRoleNames()->first() ?? 'Digital Indoor Inc.' }}
                            </p>
                        </div>
                    </div>
                </a>

                <!-- Navigation Principale -->
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
            
            <!-- Contenu Principal -->
            <div class="flex-1 flex flex-col min-w-0">
                <main class="flex-grow p-4 sm:p-6 lg:p-10">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>