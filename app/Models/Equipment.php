<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    /**
     * Les attributs qui peuvent être assignés en masse.
     */
    protected $fillable = [
        'title',
        'type',
        'price',
        // 'quantity', // <-- SUPPRIMÉ
        'entry_date', 
        'brand',
        'features',
        'image_path',
    ];

    /**
     * Assurer que la date d'entrée est toujours traitée comme un objet Date.
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    /**
     * NOUVELLE RELATION :
     * Obtient tous les articles (numéros de série) associés à cet équipement.
     */
    public function items()
    {
        return $this->hasMany(EquipmentItem::class);
    }

    /**
     * NOUVELLE RELATION :
     * Obtient uniquement les articles qui sont actuellement "en_stock".
     */
    public function availableItems()
    {
        return $this->hasMany(EquipmentItem::class)->where('status', 'en_stock');
    }
}