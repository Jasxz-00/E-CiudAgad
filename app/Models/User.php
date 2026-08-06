<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'tracking_number',
        'pin',
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function resident()
    {
        return $this->hasOne(Resident::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPersonnel(): bool
    {
        return $this->role === 'personnel';
    }

    public function isResident(): bool
    {
        return $this->role === 'resident';
    }
}
