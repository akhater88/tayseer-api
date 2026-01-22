<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'phone',
        'code',
        'purpose',
        'attempts',
        'is_used',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // ==================== SCOPES ====================

    public function scopeValid($query)
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', now())
            ->where('attempts', '<', config('tayseer.otp.max_attempts', 3));
    }

    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    public function scopeForPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    // ==================== HELPERS ====================

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->is_used;
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= config('tayseer.otp.max_attempts', 3);
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed() && !$this->hasExceededAttempts();
    }

    public function verify(string $code): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->increment('attempts');

        if ($this->code === $code) {
            $this->update(['is_used' => true]);
            return true;
        }

        return false;
    }

    public static function generate(string $phone, string $purpose): self
    {
        // Invalidate existing OTPs
        static::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $length = config('tayseer.otp.length', 6);
        $expiry = config('tayseer.otp.expiry_minutes', 5);

        return static::create([
            'phone' => $phone,
            'code' => str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT),
            'purpose' => $purpose,
            'attempts' => 0,
            'is_used' => false,
            'expires_at' => now()->addMinutes($expiry),
            'created_at' => now(),
        ]);
    }

    public static function findValid(string $phone, string $purpose): ?self
    {
        return static::forPhone($phone)
            ->forPurpose($purpose)
            ->valid()
            ->latest('created_at')
            ->first();
    }
}
