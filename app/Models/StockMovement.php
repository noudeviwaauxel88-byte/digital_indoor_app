<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reason',
        'file_path',
        'movement_date',
        'project_id',          
        'other_destination',  
    ];

    protected function casts(): array
    {
        return [
            'movement_date' => 'date',
        ];
    }

    /**
     * L'utilisateur / bénéficiaire de la sortie.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le projet associé à cette sortie.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Les lignes de mouvement de stock associées.
     */
    public function movementItems()
    {
        return $this->hasMany(StockMovementItem::class);
    }

    /**
     * Les articles/équipements physiques concernés par ce mouvement.
     */
    public function equipmentItems()
    {
        return $this->belongsToMany(
            EquipmentItem::class, 
            'stock_movement_items', 
            'stock_movement_id', 
            'equipment_item_id'
        );
    }
}