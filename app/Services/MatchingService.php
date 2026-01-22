<?php

namespace App\Services;

use App\Enums\InterestStatus;
use App\Events\InterestAccepted;
use App\Events\InterestSent;
use App\Events\MatchCreated;
use App\Models\Block;
use App\Models\Interest;
use App\Models\Match as MatchModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MatchingService
{
    /**
     * Send interest from one user to another
     */
    public function sendInterest(User $sender, User $receiver, ?string $message = null): array
    {
        // Validation checks
        if ($sender->id === $receiver->id) {
            return ['success' => false, 'message' => 'لا يمكنك إرسال إعجاب لنفسك'];
        }

        if ($sender->gender === $receiver->gender) {
            return ['success' => false, 'message' => 'لا يمكن إرسال إعجاب لنفس الجنس'];
        }

        if (Block::existsBetween($sender, $receiver)) {
            return ['success' => false, 'message' => 'لا يمكن إرسال إعجاب لهذا المستخدم'];
        }

        // Check if already sent
        if ($sender->hasSentInterestTo($receiver)) {
            return ['success' => false, 'message' => 'لقد أرسلت إعجاباً مسبقاً لهذا المستخدم'];
        }

        // Check daily limit
        $dailyLimit = config('tayseer.daily_interest_limit', 20);
        $todayCount = Interest::where('sender_id', $sender->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $dailyLimit) {
            return ['success' => false, 'message' => 'لقد وصلت للحد الأقصى من الإعجابات اليومية'];
        }

        // Check if match already exists
        if ($sender->hasMutualInterestWith($receiver)) {
            return ['success' => false, 'message' => 'يوجد توافق مسبق مع هذا المستخدم'];
        }

        // Create interest
        $interest = Interest::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $message,
            'status' => InterestStatus::Pending,
        ]);

        event(new InterestSent($interest));

        return [
            'success' => true,
            'message' => 'تم إرسال الإعجاب بنجاح',
            'interest' => $interest,
        ];
    }

    /**
     * Accept an interest
     */
    public function acceptInterest(Interest $interest, User $user): array
    {
        if ($interest->receiver_id !== $user->id) {
            return ['success' => false, 'message' => 'غير مصرح لك بهذا الإجراء'];
        }

        if (!$interest->isPending()) {
            return ['success' => false, 'message' => 'تم الرد على هذا الإعجاب مسبقاً'];
        }

        return DB::transaction(function () use ($interest) {
            $interest->accept();

            event(new InterestAccepted($interest));

            // Check if this creates a mutual interest (match)
            $reverseInterest = Interest::where('sender_id', $interest->receiver_id)
                ->where('receiver_id', $interest->sender_id)
                ->where('status', InterestStatus::Accepted)
                ->first();

            $match = null;
            if ($reverseInterest) {
                // Create match
                $match = MatchModel::createForUsers($interest->sender, $interest->receiver);
                event(new MatchCreated($match));
            }

            return [
                'success' => true,
                'message' => $match ? 'تهانينا! إعجاب متبادل 🎉' : 'تم قبول الإعجاب',
                'interest' => $interest,
                'match' => $match,
            ];
        });
    }

    /**
     * Decline an interest
     */
    public function declineInterest(Interest $interest, User $user): array
    {
        if ($interest->receiver_id !== $user->id) {
            return ['success' => false, 'message' => 'غير مصرح لك بهذا الإجراء'];
        }

        if (!$interest->isPending()) {
            return ['success' => false, 'message' => 'تم الرد على هذا الإعجاب مسبقاً'];
        }

        $interest->decline();

        return [
            'success' => true,
            'message' => 'تم رفض الإعجاب',
            'interest' => $interest,
        ];
    }

    /**
     * Withdraw a sent interest
     */
    public function withdrawInterest(Interest $interest, User $user): array
    {
        if ($interest->sender_id !== $user->id) {
            return ['success' => false, 'message' => 'غير مصرح لك بهذا الإجراء'];
        }

        if (!$interest->isPending()) {
            return ['success' => false, 'message' => 'لا يمكن سحب إعجاب تم الرد عليه'];
        }

        $interest->withdraw();

        return [
            'success' => true,
            'message' => 'تم سحب الإعجاب',
        ];
    }

    /**
     * Get discovery feed for user
     */
    public function getDiscoveryFeed(User $user, int $perPage = 20)
    {
        $oppositeGender = $user->isMale() ? 'female' : 'male';

        return User::query()
            ->with(['profile', 'primaryPhoto'])
            ->where('gender', $oppositeGender)
            ->active()
            ->members()
            ->notBlocked($user)
            // Exclude already sent interests
            ->whereDoesntHave('receivedInterests', fn($q) => 
                $q->where('sender_id', $user->id)
            )
            // Exclude existing matches
            ->whereDoesntHave('matches', fn($q) => 
                $q->forUser($user)->active()
            )
            ->whereHas('profile', fn($q) => 
                $q->where('profile_completion', '>=', config('tayseer.profile_completion_required', 70))
            )
            ->orderByDesc('last_online_at')
            ->paginate($perPage);
    }

    /**
     * Get daily recommendations
     */
    public function getDailyRecommendations(User $user, int $limit = 5)
    {
        $oppositeGender = $user->isMale() ? 'female' : 'male';
        $profile = $user->profile;

        return User::query()
            ->with(['profile', 'primaryPhoto'])
            ->where('gender', $oppositeGender)
            ->active()
            ->members()
            ->notBlocked($user)
            ->whereDoesntHave('receivedInterests', fn($q) => 
                $q->where('sender_id', $user->id)
            )
            ->whereHas('profile', function ($q) use ($profile) {
                // Same city or country
                $q->where(function ($q) use ($profile) {
                    $q->where('city_id', $profile->city_id)
                      ->orWhere('country_id', $profile->country_id);
                });
                // Similar religious level
                $q->where('religious_level', $profile->religious_level);
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
