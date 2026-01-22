<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Match extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_1_id',
        'user_2_id',
        'matched_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_1_id');
    }

    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_2_id');
    }

    public function chatRequest(): HasOne
    {
        return $this->hasOne(ChatRequest::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_1_id', $user->id)
            ->orWhere('user_2_id', $user->id);
    }

    // ==================== HELPERS ====================

    public function getOtherUser(User $user): User
    {
        return $this->user_1_id === $user->id ? $this->user2 : $this->user1;
    }

    public function hasUser(User $user): bool
    {
        return $this->user_1_id === $user->id || $this->user_2_id === $user->id;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function end(): void
    {
        $this->update(['status' => 'ended']);
    }

    public function hasChatRequest(): bool
    {
        return $this->chatRequest()->exists();
    }

    public function hasApprovedChat(): bool
    {
        return $this->chatRequest()
            ->where('status', 'approved')
            ->exists();
    }

    public static function findBetweenUsers(User $user1, User $user2): ?self
    {
        return static::where(function ($q) use ($user1, $user2) {
            $q->where('user_1_id', $user1->id)->where('user_2_id', $user2->id);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('user_1_id', $user2->id)->where('user_2_id', $user1->id);
        })->first();
    }

    public static function createForUsers(User $user1, User $user2): self
    {
        return static::create([
            'user_1_id' => $user1->id,
            'user_2_id' => $user2->id,
            'matched_at' => now(),
            'status' => 'active',
        ]);
    }
}
