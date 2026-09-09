<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
    ];

    /**
     * Relation avec les équipements de ce type
     */
    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'equipment_type_id');
    }
}