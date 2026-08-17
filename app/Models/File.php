<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    /**
     * La propriété $fillable autorise l'assignation de masse.
     * Sans cela, File::create() renvoie une erreur de sécurité.
     */
    protected $fillable = [
        'name',
        'path',
        'size',
        'type',
        'user_id',
        'folder_id',
        'is_favorite', // Important pour la fonctionnalité "Favoris"
    ];

    /**
     * Accessor pour obtenir la taille formatée (ex: 2.5 MB)
     * Utilisation dans la vue : $file->size_formatted
     */
    public function getSizeFormattedAttribute()
    {
        $bytes = $this->size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }

    /**
     * Relation : Un fichier appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : Un fichier peut appartenir à un dossier (ou être null si à la racine).
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}