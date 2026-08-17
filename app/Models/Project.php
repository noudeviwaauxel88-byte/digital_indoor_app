<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // <--- 1. Import
use Spatie\Activitylog\LogOptions; // <--- 2. Import

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'user_id',
        'color',
        'visibility',
    ];

    /**
     * Get the user that owns the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The members that belong to the project.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    /**
     * Get the tasks for the project.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    // 4. Configuration du Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'description', 'status']) // Quels champs surveiller ?
        ->logOnlyDirty() // Ne logger que ce qui a changé
        ->dontSubmitEmptyLogs(); // Ne rien logger si rien n'a changé
    }
}
