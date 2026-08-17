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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movementItems()
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function equipmentItems()
    {
        return $this->belongsToMany(EquipmentItem::class, 'stock_movement_items');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}