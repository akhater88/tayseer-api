<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'slug',
        'username',
        'phone',
        'email',
        'password',
        'gender',
        'user_type',
        'status',
        'is_convert',
        'last_online_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'phone',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_online_at' => 'datetime',
            'password' => 'hashed',
            'gender' => Gender::class,
            'user_type' => UserType::class,
            'status' => UserStatus::class,
            'is_convert' => 'boolean',
        ];
    }

    // ==================== ROUTE KEY ====================

    /**
     * Get the route key for the model (use slug for URLs)
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->slug)) {
                $user->slug = static::generateUniqueSlug($user->username);
            }
        });
    }

    /**
     * Generate a unique slug from username
     * Format: username-xxxx (e.g., ahmed-m4k9)
     */
    public static function generateUniqueSlug(string $username): string
    {
        $baseSlug = Str::slug($username);
        $randomSuffix = Str::lower(Str::random(4));
        $slug = "{$baseSlug}-{$randomSuffix}";

        // Ensure uniqueness
        while (static::where('slug', $slug)->exists()) {
            $randomSuffix = Str::lower(Str::random(4));
            $slug = "{$baseSlug}-{$randomSuffix}";
        }

        return $slug;
    }

    // ==================== RELATIONSHIPS ====================

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(UserPhoto::class)->orderBy('sort_order');
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(UserPhoto::class)->where('is_primary', true);
    }

    public function sentInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'sender_id');
    }

    public function receivedInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'receiver_id');
    }

    public function matchesAsUser1(): HasMany
    {
        return $this->hasMany(Match::class, 'user_1_id');
    }

    public function matchesAsUser2(): HasMany
    {
        return $this->hasMany(Match::class, 'user_2_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy(): HasMany
    {
        return $this->hasMany(Favorite::class, 'favorited_user_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blockedBy(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }

    public function sentReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function receivedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    // Guardian relationships (for females)
    public function guardianInvitations(): HasMany
    {
        return $this->hasMany(GuardianInvitation::class, 'female_user_id');
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class, 'female_user_id')->where('status', 'active');
    }

    // Guardian relationships (for guardian users)
    public function wards(): HasMany
    {
        return $this->hasMany(Guardian::class, 'guardian_user_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active);
    }

    public function scopeMale($query)
    {
        return $query->where('gender', Gender::Male);
    }

    public function scopeFemale($query)
    {
        return $query->where('gender', Gender::Female);
    }

    public function scopeMembers($query)
    {
        return $query->where('user_type', UserType::Member);
    }

    public function scopeOnline($query, int $minutes = 15)
    {
        return $query->where('last_online_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeNotBlocked($query, User $user)
    {
        return $query->whereDoesntHave('blocks', fn($q) => $q->where('blocked_id', $user->id))
            ->whereDoesntHave('blockedBy', fn($q) => $q->where('blocker_id', $user->id));
    }

    // ==================== HELPERS ====================

    public function isMale(): bool
    {
        return $this->gender === Gender::Male;
    }

    public function isFemale(): bool
    {
        return $this->gender === Gender::Female;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isConvert(): bool
    {
        return $this->is_convert === true;
    }

    public function needsGuardianApproval(): bool
    {
        return $this->isFemale() && !$this->isConvert();
    }

    public function hasGuardian(): bool
    {
        return $this->guardian()->exists();
    }

    public function isOnline(): bool
    {
        return $this->last_online_at && $this->last_online_at->gt(now()->subMinutes(15));
    }

    public function updateLastOnline(): void
    {
        $this->update(['last_online_at' => now()]);
    }

    public function getAge(): int
    {
        return $this->profile?->date_of_birth?->age ?? 0;
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blocks()->where('blocked_id', $user->id)->exists();
    }

    public function isBlockedBy(User $user): bool
    {
        return $this->blockedBy()->where('blocker_id', $user->id)->exists();
    }

    public function hasSentInterestTo(User $user): bool
    {
        return $this->sentInterests()->where('receiver_id', $user->id)->exists();
    }

    public function hasReceivedInterestFrom(User $user): bool
    {
        return $this->receivedInterests()->where('sender_id', $user->id)->exists();
    }

    public function hasMutualInterestWith(User $user): bool
    {
        return Match::where(function ($q) use ($user) {
            $q->where('user_1_id', $this->id)->where('user_2_id', $user->id);
        })->orWhere(function ($q) use ($user) {
            $q->where('user_1_id', $user->id)->where('user_2_id', $this->id);
        })->exists();
    }

    /**
     * Get all matches for this user
     */
    public function getAllMatches()
    {
        return Match::where('user_1_id', $this->id)
            ->orWhere('user_2_id', $this->id);
    }
}
