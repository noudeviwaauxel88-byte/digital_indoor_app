<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description', 'start_date', 'end_date', 
        'start_time', 'end_time', 'project_id', 'other_destination',
        'activity_type', 'color', 'participants_ids', 'trainer', 
        'modules', 'intervenant', 'institution', 'location', 
        'other_details', 'file_path'
    ];

    protected $casts = [
        'participants_ids' => 'array', // Important pour stocker les IDs en JSON
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}