<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public static function register(User $user, string $token, string $platform): self
    {
        // Deactivate existing token if it belongs to another user
        static::where('token', $token)
            ->where('user_id', '!=', $user->id)
            ->update(['is_active' => false]);

        return static::updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $user->id,
                'platform' => $platform,
                'is_active' => true,
            ]
        );
    }
}
