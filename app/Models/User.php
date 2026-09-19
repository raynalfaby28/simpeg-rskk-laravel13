<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nip', 'name', 'email', 'password', 'password_cipher', 'role', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'password_cipher', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Laravel Breeze/Auth pakai NIP sebagai "username"
    public function username(): string
    {
        return 'nip';
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getDecryptedPasswordAttribute(): ?string
    {
        if (!$this->password_cipher) return null;

        try {
            return Crypt::decryptString($this->password_cipher);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
