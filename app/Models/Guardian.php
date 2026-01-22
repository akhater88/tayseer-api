<?php

namespace App\Models;

use App\Enums\GuardianRelationship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'guardian_user_id',
        'female_user_id',
        'relationship',
        'status',
        'invited_at',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'relationship' => GuardianRelationship::class,
            'invited_at' => 'datetime',
            'registered_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function guardianUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    public function femaleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'female_user_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ==================== HELPERS ====================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'registered_at' => now(),
        ]);
    }

    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }
}
