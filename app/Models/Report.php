<?php

namespace App\Models;

use App\Enums\ReportReason;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reason',
        'description',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reason' => ReportReason::class,
            'reviewed_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ==================== HELPERS ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function review(User $admin, string $status, ?string $notes = null): void
    {
        $this->update([
            'status' => $status,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }
}
