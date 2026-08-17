<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_movement_id',
        'equipment_item_id',
    ];
}