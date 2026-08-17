<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    // C'est ici qu'on autorise les champs à être remplis
    protected $fillable = [
        'name',
        'user_id',
        'parent_id',
        'is_favorite', // Ajouté pour la fonctionnalité favoris
    ];

    /**
     * Relation : Un dossier appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : Un dossier peut avoir un dossier parent.
     */
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Relation : Un dossier peut contenir des sous-dossiers.
     */
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Relation : Un dossier peut contenir des fichiers.
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }
}