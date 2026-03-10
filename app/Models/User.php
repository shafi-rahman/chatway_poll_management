<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const SUPERADMIN = 1;
    public const ADMIN = 2;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::SUPERADMIN, self::ADMIN], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::SUPERADMIN;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::SUPERADMIN => 'Superadmin',
            self::ADMIN => 'Admin',
            default => 'Unknown',
        };
    }
}