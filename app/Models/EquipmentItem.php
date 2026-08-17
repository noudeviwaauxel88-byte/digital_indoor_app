<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'serial_number',
        'status',
    ];

    /**
     * L'équipement (modèle) auquel cet article appartient.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}