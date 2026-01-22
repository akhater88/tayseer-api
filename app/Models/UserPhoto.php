<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPhoto extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'path',
        'thumbnail_path',
        'is_primary',
        'is_approved',
        'sort_order',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_approved' => 'boolean',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    public function makePrimary(): void
    {
        // Remove primary from other photos
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    public function reject(): void
    {
        $this->delete();
    }
}
