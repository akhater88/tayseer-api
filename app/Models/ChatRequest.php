<?php

namespace App\Models;

use App\Enums\ChatRequestStatus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'requester_id',
        'receiver_id',
        'status',
        'guardian_id',
        'guardian_reviewed_at',
        'guardian_decision',
        'guardian_rejection_reason',
        'firebase_conversation_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChatRequestStatus::class,
            'guardian_reviewed_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function match(): BelongsTo
    {
        return $this->belongsTo(Match::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    // ==================== SCOPES ====================

    public function scopePendingFemale($query)
    {
        return $query->where('status', ChatRequestStatus::PendingFemale);
    }

    public function scopePendingGuardian($query)
    {
        return $query->where('status', ChatRequestStatus::PendingGuardian);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ChatRequestStatus::Approved);
    }

    public function scopeForGuardian($query, User $guardian)
    {
        return $query->where('guardian_id', $guardian->id);
    }

    // ==================== HELPERS ====================

    public function isPendingFemale(): bool
    {
        return $this->status === ChatRequestStatus::PendingFemale;
    }

    public function isPendingGuardian(): bool
    {
        return $this->status === ChatRequestStatus::PendingGuardian;
    }

    public function isApproved(): bool
    {
        return $this->status === ChatRequestStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === ChatRequestStatus::Rejected;
    }

    public function approveByFemale(): void
    {
        // Check if female needs guardian approval
        if ($this->receiver->needsGuardianApproval()) {
            $this->update([
                'status' => ChatRequestStatus::PendingGuardian,
                'guardian_id' => $this->receiver->guardian?->guardian_user_id,
            ]);
        } else {
            // Convert - approve directly
            $this->approve();
        }
    }

    public function rejectByFemale(): void
    {
        $this->update([
            'status' => ChatRequestStatus::Rejected,
        ]);
    }

    public function approveByGuardian(): void
    {
        $this->update([
            'status' => ChatRequestStatus::Approved,
            'guardian_reviewed_at' => now(),
            'guardian_decision' => 'approved',
        ]);
    }

    public function rejectByGuardian(?string $reason = null): void
    {
        $this->update([
            'status' => ChatRequestStatus::Rejected,
            'guardian_reviewed_at' => now(),
            'guardian_decision' => 'rejected',
            'guardian_rejection_reason' => $reason,
        ]);
    }

    public function approve(): void
    {
        $this->update([
            'status' => ChatRequestStatus::Approved,
        ]);
    }

    public function setFirebaseConversation(string $conversationId): void
    {
        $this->update(['firebase_conversation_id' => $conversationId]);
    }
}
