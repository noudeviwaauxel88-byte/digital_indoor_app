<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';

    protected $fillable = [
        'title',
        'type',
        'price',
        'entry_date',
        'brand',
        'features',
        'image_path',
    ];

    /**
     * Tous les exemplaires / articles associés à cet équipement.
     */
    public function items()
    {
        return $this->hasMany(EquipmentItem::class, 'equipment_id');
    }

    /**
     * Uniquement les articles disponibles ('en_stock').
     */
    public function availableItems()
    {
        return $this->hasMany(EquipmentItem::class, 'equipment_id')->where('status', 'en_stock');
    }

    /**
     * Uniquement les articles sortis du stock ('sorti').
     */
    public function outItems()
    {
        return $this->hasMany(EquipmentItem::class, 'equipment_id')->where('status', 'sorti');
    }
}