<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PENULIS = 'penulis';

    public const ROLE_PEMBACA = 'pembaca';

    public const ROLES = [self::ROLE_ADMIN, self::ROLE_PENULIS, self::ROLE_PEMBACA];

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
    // Pendaftar dari halaman Daftar menjadi pembaca; penulis ditunjuk admin.
    protected $attributes = [
        'role' => self::ROLE_PEMBACA,
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

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Artikel yang disimpan untuk "Baca Nanti" (tabel pivot bookmarks). */
    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'bookmarks')->withTimestamps();
    }

    public function hasBookmarked(Article $article): bool
    {
        return $this->bookmarks()->whereKey($article->getKey())->exists();
    }

    public function scopeWithRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Admin & penulis boleh masuk area penulis (dashboard, CRUD artikel); pembaca tidak. */
    public function canWrite(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_PENULIS], true);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_PENULIS => 'Penulis',
            default => 'Pembaca',
        };
    }
}
