<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    public static function existsBetween(User $user1, User $user2): bool
    {
        return static::where(function ($q) use ($user1, $user2) {
            $q->where('blocker_id', $user1->id)->where('blocked_id', $user2->id);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('blocker_id', $user2->id)->where('blocked_id', $user1->id);
        })->exists();
    }
}
