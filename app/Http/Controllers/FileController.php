<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Liste des dossiers et fichiers de la racine (optimisé).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Sélection stricte des colonnes pour les dossiers[cite: 19]
        $queryFolders = Folder::select('id', 'user_id', 'parent_id', 'name', 'is_private', 'updated_at', 'created_at')
            ->whereNull('parent_id')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_private', false);
            });

        // Sélection stricte des colonnes existantes pour les fichiers[cite: 19]
        $queryFiles = File::select('id', 'user_id', 'folder_id', 'name', 'path', 'size', 'is_private', 'updated_at', 'created_at')
            ->whereNull('folder_id')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_private', false);
            });

        $this->applyFiltersAndSort($request, $queryFolders, $queryFiles);

        $folders = $queryFolders->with('user:id,firstname,lastname')->get();
        $files = $queryFiles->with('user:id,firstname,lastname')->get();
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

        $queryFiles = File::select('id', 'user_id', 'folder_id', 'name', 'path', 'size', 'is_private', 'updated_at', 'created_at')
            ->where('folder_id', $folder->id);

        $this->applyFiltersAndSort($request, $queryFolders, $queryFiles);

        $folders = $queryFolders->with('user:id,firstname,lastname')->get();
        $files = $queryFiles->with('user:id,firstname,lastname')->get();
        $itemsCount = $folders->count() + $files->count();

        return view('files.show', compact('folder', 'folders', 'files', 'itemsCount'));
    }

    /**
     * Créer un nouveau dossier.
     */
    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'is_private' => 'nullable|boolean',
        ]);

        Folder::create([
            'name' => trim($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'user_id' => Auth::id(),
            'is_private' => $request->boolean('is_private', false),
        ]);

        return back()->with('success', 'Dossier créé avec succès.');
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
     * Renommer un fichier ou un dossier.
     */
    public function rename(Request $request, string $type, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        if ($type === 'folder') {
            $folder = Folder::findOrFail($id);
            if ($folder->user_id !== $user->id && !$user->hasAnyRole(['SuperAdmin', 'Admin'])) {
                abort(403, 'Action non autorisée.');
            }
            $folder->update(['name' => trim($validated['name'])]);
            return back()->with('success', 'Dossier renommé avec succès.');
        }

        if ($type === 'file') {
            $file = File::findOrFail($id);
            if ($file->user_id !== $user->id && !$user->hasAnyRole(['SuperAdmin', 'Admin'])) {
                abort(403, 'Action non autorisée.');
            }
            $file->update(['name' => trim($validated['name'])]);
            return back()->with('success', 'Fichier renommé avec succès.');
        }

        abort(400, 'Type invalide.');
    }

    /**
     * Supprimer un fichier ou un dossier (avec suppression récursive du contenu).
     */
    public function destroy(string $type, int $id)
    {
        $user = Auth::user();

        if ($type === 'file') {
            $file = File::findOrFail($id);

            if ($file->user_id !== $user->id && !$user->hasAnyRole(['SuperAdmin', 'Admin'])) {
                abort(403, 'Vous n\'êtes pas autorisé à supprimer ce fichier.');
            }

            DB::transaction(function () use ($file) {
                if ($file->path && Storage::disk('public')->exists($file->path)) {
                    Storage::disk('public')->delete($file->path);
                }
                $file->delete();
            });

            return back()->with('success', 'Fichier supprimé avec succès.');
        }

        if ($type === 'folder') {
            $folder = Folder::findOrFail($id);

            if ($folder->user_id !== $user->id && !$user->hasAnyRole(['SuperAdmin', 'Admin'])) {
                abort(403, 'Vous n\'êtes pas autorisé à supprimer ce dossier.');
            }

            DB::transaction(function () use ($folder) {
                $this->deleteFolderRecursively($folder);
            });

            return back()->with('success', 'Dossier supprimé avec succès.');
        }

        abort(400, 'Type invalide.');
    }

    /**
     * Helper récursif pour supprimer fichiers et sous-dossiers d'un dossier.
     */
    private function deleteFolderRecursively(Folder $folder): void
    {
        $files = File::where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            if ($file->path && Storage::disk('public')->exists($file->path)) {
                Storage::disk('public')->delete($file->path);
            }
            $file->delete();
        }

        $subFolders = Folder::where('parent_id', $folder->id)->get();
        foreach ($subFolders as $subFolder) {
            $this->deleteFolderRecursively($subFolder);
        }

        $folder->delete();
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
                            $q->orWhere('name', 'ilike', "%.$ext");
                        }
                    });
                }
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $queryFolders->where('name', 'ilike', "%{$search}%");
            $queryFiles->where('name', 'ilike', "%{$search}%");
        }

        // Tri autorisé[cite: 19]
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