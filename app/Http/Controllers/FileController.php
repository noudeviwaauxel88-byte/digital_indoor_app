<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Affiche tous les fichiers et dossiers (Mode Public / Partagé)
     */
    public function index(Request $request)
    {
        $queryFolders = Folder::whereNull('parent_id');
        $queryFiles = File::whereNull('folder_id');

        $this->applyFiltersAndSort($request, $queryFolders, $queryFiles);

        $folders = $queryFolders->with('user')->get();
        $files = $queryFiles->with('user')->get();
        
        $itemsCount = $folders->count() + $files->count();

        return view('files.index', compact('folders', 'files', 'itemsCount'));
    }

    /**
     * Ouvre un dossier spécifique
     */
    public function show(Request $request, Folder $folder)
    {
        $queryFolders = Folder::where('parent_id', $folder->id);
        $queryFiles = File::where('folder_id', $folder->id);

        $this->applyFiltersAndSort($request, $queryFolders, $queryFiles);

        $folders = $queryFolders->with('user')->get();
        $files = $queryFiles->with('user')->get();
        $itemsCount = $folders->count() + $files->count();

        return view('files.show', compact('folder', 'folders', 'files', 'itemsCount'));
    }

    private function applyFiltersAndSort(Request $request, $queryFolders, $queryFiles)
    {
        // 1. Recherche
        if ($request->filled('search')) {
            $queryFolders->where('name', 'like', "%{$request->search}%");
            $queryFiles->where('name', 'like', "%{$request->search}%");
        }

        // 2. Filtre par Type
        if ($request->filled('types')) {
            $types = $request->types;
            $queryFiles->where(function($q) use ($types) {
                foreach ($types as $type) {
                    switch ($type) {
                        case 'Images': $q->orWhereIn('type', ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']); break;
                        case 'PDF': $q->orWhere('type', 'pdf'); break;
                        case 'Documents': $q->orWhereIn('type', ['doc', 'docx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx']); break;
                        case 'Vidéos': $q->orWhereIn('type', ['mp4', 'avi', 'mov', 'mkv']); break;
                        case 'Audios': $q->orWhereIn('type', ['mp3', 'wav', 'ogg']); break;
                        case 'Archives (zip)': $q->orWhereIn('type', ['zip', 'rar', '7z']); break;
                    }
                }
            });
            if (count($types) > 0) {
                $queryFolders->where('id', -1);
            }
        }

        // 3. Filtre par Date
        if ($request->filled('date_filter')) {
            $date = \Carbon\Carbon::parse($request->date_filter);
            $queryFolders->whereDate('created_at', $date);
            $queryFiles->whereDate('created_at', $date);
        }

        // 4. Tri
        $sort = $request->input('sort', 'Date de téléchargement');
        $direction = $request->input('direction', 'desc');

        switch ($sort) {
            case 'Nom':
                $queryFolders->orderBy('name', $direction);
                $queryFiles->orderBy('name', $direction);
                break;
            case 'Taille du fichier':
                $queryFolders->orderBy('name', 'asc'); 
                $queryFiles->orderBy('size', $direction);
                break;
            case 'Dernière ouverture':
                $queryFolders->orderBy('updated_at', $direction);
                $queryFiles->orderBy('updated_at', $direction);
                break;
            default: // Date de téléchargement
                $queryFolders->orderBy('created_at', $direction);
                $queryFiles->orderBy('created_at', $direction);
                break;
        }
    }

    public function storeFolder(Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        Folder::create(['name' => $request->name, 'user_id' => Auth::id(), 'parent_id' => $request->parent_id]);
        return back()->with('success', 'Dossier créé.');
    }

    public function storeFile(Request $request) {
        $request->validate(['file' => 'required|file|max:51200']);
        $file = $request->file('file');
        $path = $file->store('drive_files', 'public');
        File::create([
            'name' => $file->getClientOriginalName(), 
            'path' => $path, 
            'size' => $file->getSize(), 
            'type' => $file->extension(), 
            'user_id' => Auth::id(), 
            'folder_id' => $request->folder_id
        ]);
        return back()->with('success', 'Fichier téléchargé.');
    }

    public function download(File $file) {
        return Storage::disk('public')->download($file->path, $file->name);
    }

    /**
     * Renommer un fichier ou dossier.
     * Autorisé pour le propriétaire OU le SuperAdmin.
     */
    public function rename(Request $request, $type, $id) {
        $model = $type === 'folder' ? Folder::findOrFail($id) : File::findOrFail($id);
        
        // SÉCURITÉ MISE À JOUR :
        // Si je ne suis PAS le propriétaire ET que je ne suis PAS SuperAdmin => ERREUR
        if($model->user_id !== Auth::id() && !Auth::user()->hasRole('SuperAdmin')) {
            abort(403, 'VOUS NE POUVEZ RENOMMER QUE VOS PROPRES FICHIERS.');
        }
        
        $model->update(['name' => $request->name]);
        return back()->with('success', 'Renommé.');
    }

    public function toggleFavorite($type, $id) {
        $model = $type === 'folder' ? Folder::findOrFail($id) : File::findOrFail($id);
        // Les favoris restent personnels
        if($model->user_id !== Auth::id()) abort(403);
        
        $model->update(['is_favorite' => !$model->is_favorite]);
        return back();
    }

    /**
     * Supprimer un fichier ou dossier.
     * Autorisé pour le propriétaire OU le SuperAdmin.
     */
    public function destroy($type, $id) {
        if ($type === 'folder') { 
            $folder = Folder::findOrFail($id);
            
            // SÉCURITÉ MISE À JOUR
            if($folder->user_id !== Auth::id() && !Auth::user()->hasRole('SuperAdmin')) {
                abort(403, 'Action non autorisée sur le dossier d\'un autre utilisateur.');
            }
            
            $folder->delete(); 
        } 
        else { 
            $f = File::findOrFail($id); 
            
            // SÉCURITÉ MISE À JOUR
            if($f->user_id !== Auth::id() && !Auth::user()->hasRole('SuperAdmin')) {
                abort(403, 'Action non autorisée sur le fichier d\'un autre utilisateur.');
            }
            
            if(Storage::disk('public')->exists($f->path)) Storage::disk('public')->delete($f->path);
            $f->delete(); 
        }
        return back()->with('success', 'Supprimé.');
    }
}