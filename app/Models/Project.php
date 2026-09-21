<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'user_id',
        'color',
        'visibility',
        'structure',
        'file_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->orderBy('firstname')
                    ->orderBy('lastname');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Accesseur dynamique pour vérifier si le projet est en retard.
     */
    public function getIsLateAttribute(): bool
    {
        return $this->status !== 'completed' 
            && $this->end_date 
            && Carbon::parse($this->end_date)->isPast();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'status', 'structure', 'file_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}