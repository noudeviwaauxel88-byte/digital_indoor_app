<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // <--- LIGNE AJOUTÉE (1/2)

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles; // <--- LIGNE AJOUTÉE (2/2)

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'phone',
        'email',
        'password',
        // J'ai ajouté ces champs optionnels au cas où tu en aurais besoin plus tard
        'avatar',
        'job_title',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor to dynamically create the 'name' attribute.
     * This ensures compatibility with other parts of the application.
     */
    public function getNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * The user has created multiple projects.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * The user is a member of multiple projects.
     */
    public function memberOfProjects()
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }
}