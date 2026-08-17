<x-app-layout>
    <div class="p-4 sm:p-6" x-data="fileManager()">
        
        {{-- FORMULAIRE GLOBAL DE FILTRES & TRI --}}
        <form action="{{ route('files.index') }}" method="GET" x-ref="filterForm" id="filterForm">
            
            {{-- CHAMPS CACHÉS POUR L'ÉTAT --}}
            <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'Date de téléchargement') }}">
            <input type="hidden" name="direction" id="directionInput" value="{{ request('direction', 'desc') }}">
            <input type="hidden" name="date_filter" id="dateInput" value="{{ request('date_filter') }}">

            {{-- HEADER : Titre + Actions --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Mon Drive</h1>
                
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.away="open = false" class="px-4 py-2 bg-[#4b49ac] text-white font-semibold rounded-lg shadow hover:bg-[#4b49ac]/90 flex items-center gap-2 transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Ajouter nouveau
                        </button>
                        <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 z-20 py-1" style="display: none;">
                            <button type="button" @click="isUploadModalOpen = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Télécharger
                            </button>
                            <button type="button" @click="isCreateFolderModalOpen = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg> Créer dossier
                            </button>
                        </div>
                    </div>
                    
                    <div class="relative flex-grow sm:flex-grow-0 w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="w-full pl-9 pr-4 py-2 bg-gray-100 border-transparent focus:bg-white focus:border-gray-300 rounded-lg text-sm transition-colors">
                    </div>
                </div>
            </div>

            {{-- BARRE D'OUTILS --}}
            <div class="flex flex-wrap justify-between items-center mb-4 gap-4 relative z-10">
                <div class="flex items-center gap-4">
                    <span class="text-gray-500 text-xs">{{ $itemsCount ?? 0 }} éléments</span>
                    @if(request('date_filter'))
                        <button type="button" @click="document.getElementById('dateInput').value = ''; document.getElementById('filterForm').submit()" class="flex items-center gap-2 px-3 py-1 bg-purple-50 text-purple-700 text-xs font-medium rounded hover:bg-purple-100 transition-colors group">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse(request('date_filter'))->format('d M Y') }}
                            <span class="ml-1 text-purple-400 hover:text-purple-900 font-bold">×</span>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    
                    {{-- MENU FILTRE --}}
                    <div class="relative" x-data="{ open: false, submenu: null }">
                        <button type="button" @click="open = !open" @click.away="open = false; submenu = null" class="flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded hover:bg-gray-200 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg> Filtre
                        </button>
                        
                        <div x-show="open" class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-20" style="display: none;">
                            
                            <!-- Taper -->
                            <div class="relative" @mouseenter="submenu = 'type'">
                                <button type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between">
                                    <span class="flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg> Taper</span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                                <div x-show="submenu === 'type'" class="absolute right-full top-0 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-2 mr-2">
                                    @foreach(['Documents', 'PDF', 'Vidéos', 'Images', 'Audios', 'Archives (zip)'] as $type)
                                        <label class="flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" name="types[]" value="{{ $type }}" @if(is_array(request('types')) && in_array($type, request('types'))) checked @endif @change="document.getElementById('filterForm').submit()" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500">
                                            <span class="ml-2 text-sm text-gray-700">{{ $type }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Date de téléchargement (Avec Calendrier JS) -->
                            <div class="relative" @mouseenter="submenu = 'upload_date'">
                                <button type="button" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 justify-between">
                                    <span class="flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Date de téléchargement</span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                                
                                <!-- Composant Calendrier -->
                                <div x-show="submenu === 'upload_date'" 
                                     x-data="datepicker()" 
                                     x-init="initDate()"
                                     class="absolute right-full top-0 w-64 bg-white rounded-lg shadow-xl border border-gray-100 p-4 mr-2 text-center">
                                    
                                    <div class="flex justify-between items-center mb-4">
                                        <button type="button" @click.stop="prevMonth()" class="p-1 hover:bg-gray-100 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                                        <span class="font-bold text-sm text-gray-800" x-text="MONTH_NAMES[month] + ' ' + year"></span>
                                        <button type="button" @click.stop="nextMonth()" class="p-1 hover:bg-gray-100 rounded"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
                                    </div>
                                    
                                    <div class="grid grid-cols-7 gap-1 text-xs text-gray-400 mb-2">
                                        <template x-for="day in DAYS">
                                            <div x-text="day.substring(0,1)"></div>
                                        </template>
                                    </div>
                                    
                                    <div class="grid grid-cols-7 gap-1 text-sm text-gray-700">
                                        <template x-for="blank in blankdays">
                                            <div class="w-7 h-7"></div>
                                        </template>
                                        <template x-for="date in no_of_days">
                                            <div @click="selectDate(date)" 
                                                 class="w-7 h-7 flex items-center justify-center rounded-full cursor-pointer text-xs"
                                                 :class="isSelected(date) ? 'bg-[#4b49ac] text-white' : 'hover:bg-purple-50'">
                                                <span x-text="date"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('files.index') }}" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Tout effacer</a>
                        </div>
                    </div>

                    {{-- MENU TRÈVES (Tri) --}}
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.away="open = false" class="flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded hover:bg-gray-200 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg> Trèves
                        </button>
                        <div x-show="open" class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-20" style="display: none;">
                            @foreach(['Nom', 'Taille du fichier', 'Dernière ouverture', 'Date de téléchargement'] as $sortOption)
                                <button type="button" 
                                        @click="document.getElementById('sortInput').value = '{{ $sortOption }}'; 
                                                document.getElementById('filterForm').submit()" 
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex justify-between items-center">
                                    <span>{{ $sortOption }}</span>
                                    @if(request('sort') == $sortOption)
                                        <span class="text-xs text-[#4b49ac] font-bold">✓</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- CONTENU PRINCIPAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 min-h-[600px] p-4 relative z-0">
            @if(($itemsCount ?? 0) == 0 && !request('search') && !request('types') && !request('date_filter'))
                {{-- ÉTAT VIDE --}}
                <div class="h-full flex flex-col items-center justify-center text-center py-20">
                    <div class="mb-6"><svg width="200" height="160" viewBox="0 0 284 215" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="100" y="80" width="80" height="100" rx="2" stroke="#4B5563" stroke-width="2" fill="white"/><text x="130" y="140" fill="#9CA3AF" font-size="12">Vide</text></svg></div>
                    <h2 class="text-xl font-bold text-gray-700 mb-2">Votre espace de stockage est vide !</h2>
                    <div class="flex justify-center gap-3 mt-6">
                        <button @click="isUploadModalOpen = true" class="flex items-center gap-2 px-5 py-2 bg-[#4b49ac] text-white font-semibold rounded-lg shadow hover:bg-[#4b49ac]/90 transition-colors text-sm">Télécharger</button>
                        <button @click="isCreateFolderModalOpen = true" class="flex items-center gap-2 px-5 py-2 bg-white border border-gray-200 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition-colors text-sm">Créer dossier</button>
                    </div>
                </div>
            @else
                {{-- LISTE --}}
                <div class="w-full text-left h-full">
                    {{-- DOSSIERS --}}
                    @if($folders->count() > 0)
                    <div class="mb-6">
                        <p class="text-sm font-bold text-gray-800 mb-3">Dossiers ({{ $folders->count() }})</p>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            @foreach($folders as $folder)
                                <div class="relative group" x-data="{ menuOpen: false }">
                                    <a href="{{ route('folders.show', $folder) }}" class="block p-3 bg-white border border-gray-200 rounded-xl hover:border-purple-300 hover:shadow-md transition-all">
                                        <div class="flex items-start justify-between mb-2">
                                            <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                            <button @click.prevent="menuOpen = !menuOpen" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg></button>
                                        </div>
                                        <h3 class="font-medium text-gray-800 truncate text-sm">{{ $folder->name }}</h3>
                                        <p class="text-[10px] text-gray-400 mt-1">{{ $folder->created_at->format('d/m/Y H:i') }}</p>
                                    </a>
                                    <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute top-8 right-2 w-52 bg-white rounded-lg shadow-xl border border-gray-100 z-20 py-1" style="display: none;">
                                        <button @click="openRename('folder', {{ $folder->id }}, '{{ $folder->name }}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Renommer</button>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <form action="{{ route('files.destroy', ['type' => 'folder', 'id' => $folder->id]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Supprimer</button></form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- FICHIERS --}}
                    @if($files->count() > 0)
                    <div>
                        <p class="text-sm font-bold text-gray-800 mb-3">Fichiers ({{ $files->count() }})</p>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            @foreach($files as $file)
                                <div @click="openPreview({{ json_encode($file) }})" class="p-3 bg-gray-50 border border-gray-100 rounded-xl flex items-center gap-3 hover:bg-white hover:shadow-sm transition-all cursor-pointer">
                                    <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center shadow-sm text-gray-500 border"><span class="text-[10px] font-bold uppercase">{{ $file->type ?? 'DOC' }}</span></div>
                                    <div class="flex-1 min-w-0"><p class="font-medium text-gray-800 truncate text-sm" title="{{ $file->name }}">{{ $file->name }}</p><p class="text-[10px] text-gray-400">{{ $file->created_at->format('d/m/Y') }}</p></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- MODALES EXISTANTES (Code non modifié pour gain de place, déjà fourni) --}}
        <div x-show="isCreateFolderModalOpen" x-cloak class="relative z-50"><div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div><div class="fixed inset-0 z-10 w-screen overflow-y-auto"><div class="flex min-h-full items-center justify-center p-4"><div @click.away="isCreateFolderModalOpen = false" class="relative w-full max-w-sm transform rounded-2xl bg-white text-left shadow-xl transition-all p-6"><div class="flex justify-between items-center mb-4"><h3 class="text-lg font-bold text-gray-900">Créer un dossier</h3><button @click="isCreateFolderModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button></div><form action="{{ route('folders.store') }}" method="POST">@csrf<div class="mb-6"><label class="block text-sm font-medium text-gray-700 mb-1">Nom du dossier</label><input type="text" name="name" placeholder="Entrez un nom" class="w-full px-4 py-2 border border-purple-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm" required></div><div class="flex gap-3"><button type="button" @click="isCreateFolderModalOpen = false" class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm">Annuler</button><button type="submit" class="flex-1 px-4 py-2 bg-[#4b49ac] text-white rounded-lg hover:bg-[#4b49ac]/90 font-medium text-sm">Créer</button></div></form></div></div></div></div>
        <div x-show="isUploadModalOpen" x-cloak class="relative z-50"><div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div><div class="fixed inset-0 z-10 w-screen overflow-y-auto"><div class="flex min-h-full items-center justify-center p-4"><div @click.away="isUploadModalOpen = false" class="relative w-full max-w-lg transform rounded-2xl bg-white text-left shadow-xl transition-all p-6"><div class="flex justify-between items-center mb-6"><h3 class="text-xl font-bold text-gray-900">Télécharger un fichier</h3><button @click="isUploadModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button></div><form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">@csrf<div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center mb-6 bg-gray-50 hover:bg-gray-100 transition cursor-pointer relative"><input type="file" name="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="this.nextElementSibling.innerText = this.files[0].name"><p class="text-gray-500 pointer-events-none">Cliquez pour sélectionner</p></div><button type="submit" class="w-full py-2.5 bg-[#4b49ac] text-white font-semibold rounded-lg shadow hover:bg-[#4b49ac]/90 transition-colors">Télécharger</button></form></div></div></div></div>
        <div x-show="isRenameModalOpen" x-cloak class="relative z-50"><div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div><div class="fixed inset-0 z-10 w-screen overflow-y-auto"><div class="flex min-h-full items-center justify-center p-4"><div @click.away="isRenameModalOpen = false" class="relative w-full max-w-sm bg-white rounded-xl shadow-xl p-6"><div class="flex justify-between items-center mb-4"><h3 class="text-lg font-bold text-gray-900">Renommer</h3><button @click="isRenameModalOpen = false" class="text-gray-400">&times;</button></div><form x-bind:action="'/fichiers/rename/' + renameType + '/' + renameId" method="POST">@csrf @method('PATCH')<input type="text" name="name" x-model="renameName" class="w-full px-4 py-2 border rounded-lg text-sm mb-6" required><div class="flex gap-3"><button type="button" @click="isRenameModalOpen = false" class="flex-1 px-4 py-2 border rounded-lg text-sm">Annuler</button><button type="submit" class="flex-1 px-4 py-2 bg-[#4b49ac] text-white rounded-lg text-sm">Sauvegarder</button></div></form></div></div></div></div>
        <div x-show="isPreviewModalOpen" x-cloak class="relative z-50"><div class="fixed inset-0 bg-black/60 transition-opacity"></div><div class="fixed inset-0 z-10 w-screen h-screen overflow-hidden"><div class="flex items-center justify-center h-full p-4"><div @click.away="isPreviewModalOpen = false" class="relative w-full max-w-5xl h-[85vh] bg-white rounded-lg shadow-2xl flex flex-col"><div class="flex justify-between items-center px-4 py-3 border-b border-gray-200 bg-white rounded-t-lg"><div class="flex items-center gap-3 overflow-hidden"><div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center text-gray-500 shrink-0 font-bold text-xs" x-text="previewFile ? previewFile.type : ''"></div><div class="min-w-0"><h3 class="text-sm font-semibold text-gray-800 truncate" x-text="previewFile ? previewFile.name : ''"></h3><p class="text-xs text-gray-500">Mon Drive</p></div></div><div class="flex items-center gap-2"><a :href="'/fichiers/download/' + (previewFile ? previewFile.id : '')" class="flex items-center gap-1 px-3 py-1.5 border border-gray-300 rounded-md text-gray-700 text-xs hover:bg-gray-50"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Télécharger</a><button @click="isPreviewModalOpen = false" class="p-1.5 hover:bg-gray-100 rounded text-gray-500">&times;</button></div></div><div class="flex-1 bg-gray-100 overflow-auto flex items-center justify-center p-4"><template x-if="previewFile"><div class="bg-white shadow-lg transition-transform origin-top" :style="'transform: scale(' + zoom + ')'"><template x-if="['jpg','jpeg','png','gif'].includes(previewFile.type.toLowerCase())"><img :src="'/storage/' + previewFile.path" class="max-w-full max-h-[600px] object-contain"></template><template x-if="previewFile.type.toLowerCase() === 'pdf'"><iframe :src="'/storage/' + previewFile.path" class="w-[800px] h-[600px]"></iframe></template><template x-if="!['jpg','jpeg','png','gif','pdf'].includes(previewFile.type.toLowerCase())"><div class="p-20 text-center text-gray-500">Aperçu non disponible</div></template></div></template></div></div></div></div></div>

    </div>
    
    {{-- LOGIQUE JAVASCRIPT --}}
    <script>
        function fileManager() {
            return {
                isUploadModalOpen: false, isCreateFolderModalOpen: false, isPreviewModalOpen: false, isRenameModalOpen: false,
                previewFile: null, renameType: '', renameId: '', renameName: '', zoom: 1,
                openPreview(f) { this.previewFile = f; this.zoom = 1; this.isPreviewModalOpen = true; },
                openRename(type, id, name) { this.renameType = type; this.renameId = id; this.renameName = name; this.isRenameModalOpen = true; },
                printFile() { if(this.previewFile) window.open('/storage/' + this.previewFile.path, '_blank').print(); }
            }
        }

        function datepicker() {
            return {
                MONTH_NAMES: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                DAYS: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                month: '',
                year: '',
                no_of_days: [],
                blankdays: [],
                
                initDate() {
                    let today = new Date();
                    this.month = today.getMonth();
                    this.year = today.getFullYear();
                    this.getNoOfDays();
                },

                isSelected(date) {
                    const d = new Date(this.year, this.month, date);
                    let dateStr = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
                    return document.getElementById('dateInput').value === dateStr;
                },

                getNoOfDays() {
                    let daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
                    let dayOfWeek = new Date(this.year, this.month).getDay();

                    let blankdaysArray = [];
                    for ( var i=1; i <= dayOfWeek; i++) { blankdaysArray.push(i); }

                    let daysArray = [];
                    for ( var i=1; i <= daysInMonth; i++) { daysArray.push(i); }

                    this.blankdays = blankdaysArray;
                    this.no_of_days = daysArray;
                },
                
                nextMonth() {
                    if (this.month == 11) { this.month = 0; this.year++; } else { this.month++; }
                    this.getNoOfDays();
                },
                
                prevMonth() {
                    if (this.month == 0) { this.month = 11; this.year--; } else { this.month--; }
                    this.getNoOfDays();
                },

                selectDate(date) {
                    let dateStr = this.year + '-' + ('0' + (this.month + 1)).slice(-2) + '-' + ('0' + date).slice(-2);
                    document.getElementById('dateInput').value = dateStr;
                    document.getElementById('filterForm').submit();
                }
            }
        }
    </script>
</x-app-layout>