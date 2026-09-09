<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image'];

    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'equipment_type_id');
    }
}