<x-app-layout>
    <div class="p-4 sm:p-6" x-data="fileManager()">
        
        {{-- HEADER DOSSIER --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-4">
            <div class="flex items-center gap-4 w-full sm:w-auto">
                {{-- Bouton Retour --}}
                <a href="{{ $folder->parent_id ? route('folders.show', $folder->parent_id) : route('files.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-50 shadow-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                    <h1 class="text-2xl font-bold text-gray-800 truncate max-w-xs sm:max-w-md">{{ $folder->name }}</h1>
                    
                    {{-- Menu contextuel du dossier courant --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg></button>
                        <div x-show="open" class="absolute left-0 top-8 w-56 bg-white rounded-lg shadow-xl border border-gray-100 z-20 py-1" style="display: none;">
                            <button @click="openRename('folder', {{ $folder->id }}, '{{ $folder->name }}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Renommer
                            </button>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form action="{{ route('files.destroy', ['type' => 'folder', 'id' => $folder->id]) }}" method="POST" onsubmit="return confirm('Supprimer ce dossier et tout son contenu ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="px-4 py-2 bg-[#4b49ac] text-white font-semibold rounded-lg shadow hover:bg-[#4b49ac]/90 flex items-center gap-2 text-sm transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Ajouter nouveau
                    </button>
                    <div x-show="open" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-100 z-20 py-1" style="display: none;">
                        <button @click="isUploadModalOpen = true; open = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Télécharger un fichier
                        </button>
                        <button @click="isCreateFolderModalOpen = true; open = false" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg> Créer un dossier
                        </button>
                    </div>
                </div>
                
                <form action="{{ route('folders.show', $folder) }}" method="GET" class="relative flex-grow sm:flex-grow-0 w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="w-full pl-9 pr-4 py-2 bg-gray-100 border-transparent focus:bg-white focus:border-gray-300 rounded-lg text-sm transition-colors">
                </form>
            </div>
        </div>

        {{-- CONTENU PRINCIPAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 min-h-[500px] p-4 relative">
            
            @if($itemsCount == 0)
                {{-- ÉTAT VIDE --}}
                <div class="h-full flex flex-col items-center justify-center text-center py-20">
                    <div class="mb-6 opacity-75">
                        {{-- Illustration "Boite ouverte" --}}
                        <svg width="150" height="120" viewBox="0 0 284 215" fill="none" xmlns="http://www.w3.org/2000/svg">
                             <path d="M142 40L42 90L142 140L242 90L142 40Z" stroke="#9CA3AF" stroke-width="2" fill="white"/>
                             <path d="M42 90V160L142 210V140" stroke="#9CA3AF" stroke-width="2" fill="white"/>
                             <path d="M242 90V160L142 210" stroke="#9CA3AF" stroke-width="2" fill="white"/>
                             <path d="M142 140V210" stroke="#9CA3AF" stroke-width="2"/>
                             <line x1="100" y1="130" x2="180" y2="130" stroke="#9CA3AF" stroke-width="2" stroke-dasharray="5 5"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-700 mb-2">Le dossier est vide !</h2>
                    <div class="flex justify-center gap-3 mt-6">
                        <button @click="isUploadModalOpen = true" class="flex items-center gap-2 px-5 py-2 bg-[#4b49ac] text-white font-semibold rounded-lg shadow hover:bg-[#4b49ac]/90 transition-colors text-sm">Télécharger un fichier</button>
                        <button @click="isCreateFolderModalOpen = true" class="flex items-center gap-2 px-5 py-2 bg-white border border-gray-200 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition-colors text-sm">Créer un dossier</button>
                    </div>
                </div>
            @else
                {{-- LISTE CONTENU DOSSIER --}}
                <div class="w-full self-start text-left">
                     {{-- Grille --}}
                     <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        {{-- SOUS-DOSSIERS --}}
                        @foreach($folders as $subFolder)
                            <div class="relative group" x-data="{ menuOpen: false }">
                                <a href="{{ route('folders.show', $subFolder) }}" class="block p-3 border rounded-xl hover:border-purple-300 hover:shadow-md transition-all bg-gray-50">
                                    <div class="flex items-start justify-between mb-2">
                                        <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                        <button @click.prevent="menuOpen = !menuOpen" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                                        </button>
                                    </div>
                                    <h3 class="font-medium text-gray-800 truncate text-sm">{{ $subFolder->name }}</h3>
                                    <p class="text-[10px] text-gray-400 mt-1">{{ $subFolder->created_at->format('d/m/Y à H:i') }}</p>
                                </a>

                                {{-- MENU CONTEXTUEL SOUS-DOSSIER --}}
                                <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute top-8 right-2 w-52 bg-white rounded-lg shadow-xl border border-gray-100 z-20 py-1" style="display: none;">
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Sélecteur
                                    </button>
                                    <form action="{{ route('files.favorite', ['type' => 'folder', 'id' => $subFolder->id]) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <svg class="w-4 h-4 {{ $subFolder->is_favorite ? 'text-yellow-400 fill-current' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg> Ajouter aux favoris
                                        </button>
                                    </form>
                                    <button @click="openRename('folder', {{ $subFolder->id }}, '{{ $subFolder->name }}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Renommer
                                    </button>
                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg> Partager le dossier
                                    </button>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <form action="{{ route('files.destroy', ['type' => 'folder', 'id' => $subFolder->id]) }}" method="POST" onsubmit="return confirm('Supprimer ce dossier ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach

                        {{-- FICHIERS --}}
                        @foreach($files as $file)
                            <div @click="openPreview({{ json_encode($file) }})" class="p-3 bg-gray-50 border border-gray-100 rounded-xl flex items-center gap-3 hover:bg-white hover:shadow-sm transition-all cursor-pointer">
                                <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center shadow-sm text-gray-500 border">
                                    <span class="text-[10px] font-bold uppercase">{{ $file->type ?? 'DOC' }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 truncate text-sm" title="{{ $file->name }}">{{ $file->name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $file->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                     </div>
                </div>
            @endif
        </div>
        
        {{-- MODALES (Create, Upload, Preview, Rename) --}}
        <div x-show="isCreateFolderModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div @click.away="isCreateFolderModalOpen = false" class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl p-6">
                        <div class="flex justify-between items-center mb-4"><h3 class="text-lg font-bold text-gray-900">Créer un dossier</h3><button @click="isCreateFolderModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button></div>
                        <form action="{{ route('folders.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $folder->id }}">
                            <div class="mb-6"><label class="block text-sm font-medium text-gray-700 mb-1">Nom du dossier</label><input type="text" name="name" placeholder="Entrez un nom" class="w-full px-4 py-2 border rounded-lg text-sm" required></div>
                            <div class="flex gap-3"><button type="button" @click="isCreateFolderModalOpen = false" class="flex-1 px-4 py-2 border rounded-lg text-sm">Annuler</button><button type="submit" class="flex-1 px-4 py-2 bg-[#4b49ac] text-white rounded-lg text-sm">Créer</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="isUploadModalOpen" x-cloak class="relative z-50">
             <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div @click.away="isUploadModalOpen = false" class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl p-6">
                        <div class="flex justify-between items-center mb-6"><h3 class="text-xl font-bold">Télécharger</h3><button @click="isUploadModalOpen = false">&times;</button></div>
                        <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="folder_id" value="{{ $folder->id }}">
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center mb-6 bg-gray-50 hover:bg-gray-100 transition cursor-pointer relative">
                                <input type="file" name="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="this.nextElementSibling.innerText = this.files[0].name">
                                <p class="text-gray-500 pointer-events-none">Cliquez pour sélectionner</p>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-[#4b49ac] text-white font-semibold rounded-lg shadow">Télécharger</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODALE RENOMMER --}}
        <div x-show="isRenameModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto"><div class="flex min-h-full items-center justify-center p-4">
                <div @click.away="isRenameModalOpen = false" class="relative w-full max-w-sm bg-white rounded-xl shadow-xl p-6">
                    <div class="flex justify-between items-center mb-4"><h3 class="text-lg font-bold text-gray-900">Renommer</h3><button @click="isRenameModalOpen = false" class="text-gray-400">&times;</button></div>
                    <form x-bind:action="'/fichiers/rename/' + renameType + '/' + renameId" method="POST">
                        @csrf @method('PATCH')
                        <input type="text" name="name" x-model="renameName" class="w-full px-4 py-2 border rounded-lg text-sm mb-6" required>
                        <div class="flex gap-3"><button type="button" @click="isRenameModalOpen = false" class="flex-1 px-4 py-2 border rounded-lg text-sm">Annuler</button><button type="submit" class="flex-1 px-4 py-2 bg-[#4b49ac] text-white rounded-lg text-sm">Sauvegarder</button></div>
                    </form>
                </div>
            </div></div>
        </div>

        {{-- MODALE PREVIEW --}}
        <div x-show="isPreviewModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-black/60 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen h-screen overflow-hidden">
                <div class="flex items-center justify-center h-full p-4">
                    <div @click.away="isPreviewModalOpen = false" class="relative w-full max-w-5xl h-[85vh] bg-white rounded-lg shadow-2xl flex flex-col">
                        
                        {{-- HEADER PREVIEW --}}
                        <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200 bg-white rounded-t-lg">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center text-gray-500 shrink-0 font-bold text-xs" x-text="previewFile ? previewFile.type : ''"></div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-800 truncate" x-text="previewFile ? previewFile.name : ''"></h3>
                                    <p class="text-xs text-gray-500">Mon Drive</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a :href="'/fichiers/download/' + (previewFile ? previewFile.id : '')" class="flex items-center gap-1 px-3 py-1.5 border border-gray-300 rounded-md text-gray-700 text-xs hover:bg-gray-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Télécharger
                                </a>
                                
                                {{-- Menu Fichier dans Preview --}}
                                <div class="relative" x-data="{ fmOpen: false }">
                                    <button @click="fmOpen = !fmOpen" class="p-1.5 hover:bg-gray-100 rounded text-gray-500"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg></button>
                                    <div x-show="fmOpen" @click.away="fmOpen = false" class="absolute right-0 mt-2 w-56 bg-white rounded shadow-xl border z-50 py-1">
                                        <form :action="'/fichiers/favorite/file/' + (previewFile ? previewFile.id : '')" method="POST">
                                            @csrf @method('PATCH') <button class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg> Ajouter aux favoris</button>
                                        </form>
                                        <button @click="openRename('file', previewFile.id, previewFile.name)" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Renommer</button>
                                        <button class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg> Partager le fichier</button>
                                        <button class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Informations sur le fichier</button>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <form :action="'/fichiers/delete/file/' + (previewFile ? previewFile.id : '')" method="POST" onsubmit="return confirm('Supprimer ?')">
                                            @csrf @method('DELETE') <button class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                                <button @click="isPreviewModalOpen = false" class="p-1.5 hover:bg-gray-100 rounded text-gray-500">&times;</button>
                            </div>
                        </div>

                        {{-- TOOLBAR --}}
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex justify-between items-center">
                             <div class="flex items-center gap-2 text-gray-500 text-xs"><span>Page 1 de 1</span></div>
                             <div class="flex items-center gap-3 text-gray-500">
                                 <div class="flex items-center bg-white border border-gray-300 rounded px-2 py-0.5 text-xs"><button @click="zoom -= 0.1">-</button><span class="mx-2" x-text="Math.round(zoom * 100) + '%'"></span><button @click="zoom += 0.1">+</button></div>
                                 <button @click="printFile()" class="p-1 hover:bg-gray-200 rounded" title="Imprimer"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg></button>
                             </div>
                        </div>

                        {{-- CONTENU --}}
                        <div class="flex-1 bg-gray-100 overflow-auto flex items-center justify-center p-4" id="printArea">
                            <template x-if="previewFile">
                                <div class="bg-white shadow-lg transition-transform origin-top" :style="'transform: scale(' + zoom + ')'">
                                    <template x-if="['jpg','jpeg','png','gif'].includes(previewFile.type.toLowerCase())"><img :src="'/storage/' + previewFile.path" class="max-w-full max-h-[600px] object-contain"></template>
                                    <template x-if="previewFile.type.toLowerCase() === 'pdf'"><iframe :src="'/storage/' + previewFile.path" class="w-[800px] h-[600px]"></iframe></template>
                                    <template x-if="!['jpg','jpeg','png','gif','pdf'].includes(previewFile.type.toLowerCase())"><div class="p-20 text-center text-gray-500">Aperçu non disponible</div></template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function fileManager() {
            return {
                isUploadModalOpen: false,
                isCreateFolderModalOpen: false,
                isPreviewModalOpen: false,
                isRenameModalOpen: false,
                previewFile: null,
                renameType: '',
                renameId: '',
                renameName: '',
                zoom: 1,
                
                openPreview(file) {
                    this.previewFile = file;
                    this.zoom = 1;
                    this.isPreviewModalOpen = true;
                },
                openRename(type, id, name) {
                    this.renameType = type;
                    this.renameId = id;
                    this.renameName = name;
                    this.isRenameModalOpen = true;
                },
                printFile() {
                    if(this.previewFile) {
                        window.open('/storage/' + this.previewFile.path, '_blank').print();
                    }
                }
            }
        }
    </script>
</x-app-layout>