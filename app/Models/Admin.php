<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user can access the Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    /**
     * Check if admin has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if admin is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if admin is at least an admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}
