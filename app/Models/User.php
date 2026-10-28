<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PENULIS = 'penulis';

    /*
     * "role" sengaja TIDAK ada di $fillable: walaupun seseorang menambahkan
     * input role=admin ke form register, User::create() akan mengabaikannya.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Sama dengan default kolom di database, agar model yang baru dibuat langsung tahu perannya.
    protected $attributes = [
        'role' => self::ROLE_PENULIS,
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
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->isAdmin() ? 'Admin' : 'Penulis';
    }
}
