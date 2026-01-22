<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EducationLevel;
use App\Enums\HijabType;
use App\Enums\BeardType;
use App\Enums\MaritalStatus;
use App\Enums\ReligiousLevel;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search profiles with filters
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $oppositeGender = $user->isMale() ? 'female' : 'male';
        $perPage = min($request->query('per_page', 20), 50);

        $query = User::query()
            ->with(['profile', 'primaryPhoto'])
            ->where('gender', $oppositeGender)
            ->where('id', '!=', $user->id)
            ->active()
            ->members()
            ->notBlocked($user)
            ->whereHas('profile');

        // Country filter
        if ($request->filled('country_id')) {
            $query->whereHas('profile', fn($q) =>
                $q->where('country_id', $request->country_id)
            );
        }

        // City filter
        if ($request->filled('city_id')) {
            $query->whereHas('profile', fn($q) =>
                $q->where('city_id', $request->city_id)
            );
        }

        // Age range filter
        if ($request->filled('age_min') || $request->filled('age_max')) {
            $query->whereHas('profile', function ($q) use ($request) {
                if ($request->filled('age_min')) {
                    $maxDate = now()->subYears($request->age_min)->toDateString();
                    $q->where('date_of_birth', '<=', $maxDate);
                }
                if ($request->filled('age_max')) {
                    $minDate = now()->subYears($request->age_max + 1)->toDateString();
                    $q->where('date_of_birth', '>=', $minDate);
                }
            });
        }

        // Marital status filter
        if ($request->filled('marital_status')) {
            $query->whereHas('profile', fn($q) =>
                $q->where('marital_status', $request->marital_status)
            );
        }

        // Religious level filter
        if ($request->filled('religious_level')) {
            $query->whereHas('profile', fn($q) =>
                $q->where('religious_level', $request->religious_level)
            );
        }

        // Hijab type filter (for females)
        if ($request->filled('hijab_type') && $oppositeGender === 'female') {
            $query->whereHas('profile', fn($q) =>
                $q->where('hijab_type', $request->hijab_type)
            );
        }

        // Beard type filter (for males)
        if ($request->filled('beard_type') && $oppositeGender === 'male') {
            $query->whereHas('profile', fn($q) =>
                $q->where('beard_type', $request->beard_type)
            );
        }

        // Has children filter
        if ($request->filled('has_children')) {
            $hasChildren = filter_var($request->has_children, FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('profile', fn($q) =>
                $hasChildren
                    ? $q->where('number_of_children', '>', 0)
                    : $q->where('number_of_children', 0)->orWhereNull('number_of_children')
            );
        }

        // Education level filter
        if ($request->filled('education_level')) {
            $query->whereHas('profile', fn($q) =>
                $q->where('education_level', $request->education_level)
            );
        }

        // Nationality filter
        if ($request->filled('nationality_id')) {
            $query->whereHas('profile', fn($q) =>
                $q->where('nationality_id', $request->nationality_id)
            );
        }

        // Online only filter
        if ($request->filled('online_only') && filter_var($request->online_only, FILTER_VALIDATE_BOOLEAN)) {
            $query->online();
        }

        // Has photo filter
        if ($request->filled('has_photo') && filter_var($request->has_photo, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas('photos');
        }

        // Sort options
        $sortBy = $request->query('sort_by', 'last_online');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'profile_completion':
                $query->orderByDesc(function ($q) {
                    $q->select('profile_completion')
                        ->from('user_profiles')
                        ->whereColumn('user_profiles.user_id', 'users.id');
                });
                break;
            case 'last_online':
            default:
                $query->orderBy('last_online_at', 'desc');
                break;
        }

        $profiles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($profiles),
            'meta' => [
                'current_page' => $profiles->currentPage(),
                'last_page' => $profiles->lastPage(),
                'per_page' => $profiles->perPage(),
                'total' => $profiles->total(),
            ],
            'filters_applied' => array_filter([
                'country_id' => $request->country_id,
                'city_id' => $request->city_id,
                'age_min' => $request->age_min,
                'age_max' => $request->age_max,
                'marital_status' => $request->marital_status,
                'religious_level' => $request->religious_level,
                'hijab_type' => $request->hijab_type,
                'beard_type' => $request->beard_type,
                'has_children' => $request->has_children,
                'education_level' => $request->education_level,
            ]),
        ]);
    }

    /**
     * View single profile by slug
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        // Cannot view own profile via this endpoint
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'استخدم /me لعرض ملفك الشخصي',
            ], 422);
        }

        // Check if blocked
        if ($currentUser->hasBlocked($user) || $currentUser->isBlockedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن عرض هذا الملف الشخصي',
            ], 403);
        }

        // Check if user is active
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الملف الشخصي غير متاح',
            ], 404);
        }

        // Record profile view (optional - for analytics)
        // ProfileView::record($currentUser->id, $user->id);

        $user->load(['profile', 'photos', 'guardian']);

        // Check interest status
        $interestStatus = null;
        $sentInterest = $currentUser->sentInterests()->where('receiver_id', $user->id)->first();
        $receivedInterest = $currentUser->receivedInterests()->where('sender_id', $user->id)->first();

        if ($sentInterest) {
            $interestStatus = [
                'type' => 'sent',
                'status' => $sentInterest->status->value,
            ];
        } elseif ($receivedInterest) {
            $interestStatus = [
                'type' => 'received',
                'status' => $receivedInterest->status->value,
            ];
        }

        // Check match status
        $matchStatus = null;
        $match = $currentUser->getAllMatches()
            ->where(function ($q) use ($user) {
                $q->where('user_1_id', $user->id)
                    ->orWhere('user_2_id', $user->id);
            })
            ->first();

        if ($match) {
            $matchStatus = [
                'match_id' => $match->id,
                'status' => $match->status,
                'chat_request_status' => $match->chatRequest?->status?->value,
            ];
        }

        // Check if favorited
        $isFavorited = $currentUser->favorites()->where('favorited_user_id', $user->id)->exists();

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
            'interaction' => [
                'interest' => $interestStatus,
                'match' => $matchStatus,
                'is_favorited' => $isFavorited,
            ],
        ]);
    }
}
