<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Liste des dossiers et fichiers de la racine (optimisé).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Sélection stricte des colonnes pour les dossiers
        $queryFolders = Folder::select('id', 'user_id', 'parent_id', 'name', 'is_private', 'updated_at', 'created_at')
            ->whereNull('parent_id')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_private', false);
            });

        // Sélection stricte des colonnes pour les fichiers
        $queryFiles = File::select('id', 'user_id', 'folder_id', 'name', 'path', 'mime_type', 'size', 'is_private', 'updated_at', 'created_at')
            ->whereNull('folder_id')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_private', false);
            });

        $this->applyFiltersAndSort($request, $queryFolders, $queryFiles);

        $folders = $queryFolders->with('user:id,name,firstname')->get();
        $files = $queryFiles->with('user:id,name,firstname')->get();
        $itemsCount = $folders->count() + $files->count();

        return view('files.index', compact('folders', 'files', 'itemsCount'));
    }

    /**
     * Afficher le contenu d'un dossier spécifique (optimisé).
     */
    public function show(Request $request, Folder $folder)
    {
        $user = Auth::user();

        if ($folder->is_private && $folder->user_id !== $user->id && !$user->hasRole('SuperAdmin')) {
            abort(403, 'Vous n\'avez pas accès à ce dossier privé.');
        }

        $queryFolders = Folder::select('id', 'user_id', 'parent_id', 'name', 'is_private', 'updated_at', 'created_at')
            ->where('parent_id', $folder->id);

        $queryFiles = File::select('id', 'user_id', 'folder_id', 'name', 'path', 'mime_type', 'size', 'is_private', 'updated_at', 'created_at')
            ->where('folder_id', $folder->id);

        $this->applyFiltersAndSort($request, $queryFolders, $queryFiles);

        $folders = $queryFolders->with('user:id,name,firstname')->get();
        $files = $queryFiles->with('user:id,name,firstname')->get();
        $itemsCount = $folders->count() + $files->count();

        return view('files.show', compact('folder', 'folders', 'files', 'itemsCount'));
    }

    /**
     * Enregistrer un nouveau fichier uploadé.
     */
    public function storeFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'folder_id' => 'nullable|exists:folders,id',
            'is_private' => 'nullable|boolean',
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('uploads', 'public');

        File::create([
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploadedFile->getClientMimeType(),
            'size' => $uploadedFile->getSize(),
            'folder_id' => $request->input('folder_id'),
            'user_id' => Auth::id(),
            'is_private' => $request->boolean('is_private', false),
        ]);

        return back()->with('success', 'Fichier téléchargé avec succès.');
    }

    /**
     * Télécharger un fichier en toute sécurité.
     */
    public function download(File $file)
    {
        $user = Auth::user();

        if ($file->is_private && $file->user_id !== $user->id && !$user->hasRole('SuperAdmin')) {
            abort(403, 'Vous n\'avez pas accès à ce fichier privé.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404, 'Fichier introuvable sur le serveur.');
        }

        return Storage::disk('public')->download($file->path, $file->name);
    }

    /**
     * Appliquer les filtres et le tri de manière optimisée.
     */
    private function applyFiltersAndSort(Request $request, $queryFolders, $queryFiles)
    {
        $direction = strtolower($request->input('direction')) === 'asc' ? 'asc' : 'desc';

        if ($request->filled('type') && $request->input('type') !== 'Tous') {
            $type = $request->input('type');
            if ($type === 'Dossiers') {
                $queryFiles->whereRaw('1 = 0');
            } else {
                $queryFolders->whereRaw('1 = 0');
                $mimeGroup = match ($type) {
                    'Documents' => ['pdf', 'doc', 'docx', 'txt'],
                    'Images'    => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
                    'Vidéos'    => ['mp4', 'avi', 'mkv', 'mov'],
                    'Audio'     => ['mp3', 'wav', 'ogg'],
                    default     => []
                };

                if (!empty($mimeGroup)) {
                    $queryFiles->where(function ($q) use ($mimeGroup) {
                        foreach ($mimeGroup as $ext) {
                            $q->orWhere('name', 'like', "%.$ext");
                        }
                    });
                }
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $queryFolders->where('name', 'like', "%{$search}%");
            $queryFiles->where('name', 'like', "%{$search}%");
        }

        // Tri autorisé
        $allowedSorts = ['Nom', 'Taille du fichier', 'Dernière ouverture', 'Date de téléchargement'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'Date de téléchargement';

        match ($sort) {
            'Nom' => [
                $queryFolders->orderBy('name', $direction),
                $queryFiles->orderBy('name', $direction)
            ],
            'Taille du fichier' => [
                $queryFolders->orderBy('name', 'asc'),
                $queryFiles->orderBy('size', $direction)
            ],
            'Dernière ouverture' => [
                $queryFolders->orderBy('updated_at', $direction),
                $queryFiles->orderBy('updated_at', $direction)
            ],
            default => [
                $queryFolders->orderBy('created_at', $direction),
                $queryFiles->orderBy('created_at', $direction)
            ]
        };
    }
}