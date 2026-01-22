<?php

namespace App\Models;

use App\Enums\InterestStatus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interest extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InterestStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', InterestStatus::Pending);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', InterestStatus::Accepted);
    }

    // ==================== HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === InterestStatus::Pending;
    }

    public function isAccepted(): bool
    {
        return $this->status === InterestStatus::Accepted;
    }

    public function accept(): void
    {
        $this->update([
            'status' => InterestStatus::Accepted,
            'responded_at' => now(),
        ]);
    }

    public function decline(): void
    {
        $this->update([
            'status' => InterestStatus::Declined,
            'responded_at' => now(),
        ]);
    }

    public function withdraw(): void
    {
        $this->update([
            'status' => InterestStatus::Withdrawn,
        ]);
    }
}
