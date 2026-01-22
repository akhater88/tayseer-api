<?php

namespace App\Models;

use App\Enums\GuardianRelationship;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GuardianInvitation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'female_user_id',
        'guardian_name',
        'guardian_phone',
        'relationship',
        'invitation_code',
        'status',
        'sent_at',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'relationship' => GuardianRelationship::class,
            'sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function femaleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'female_user_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeValid($query)
    {
        return $query->whereIn('status', ['pending', 'sent'])
            ->where('expires_at', '>', now());
    }

    // ==================== HELPERS ====================

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsAccepted(): void
    {
        $this->update(['status' => 'accepted']);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('invitation_code', $code)->exists());

        return $code;
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('invitation_code', $code)
            ->valid()
            ->first();
    }
}
