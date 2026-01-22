<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get list of favorited users
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->query('per_page', 20), 50);

        $favorites = $user->favorites()
            ->with(['favoritedUser.profile', 'favoritedUser.primaryPhoto'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = $favorites->through(function ($favorite) {
            return [
                'id' => $favorite->id,
                'user' => new UserResource($favorite->favoritedUser),
                'added_at' => $favorite->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
            ],
        ]);
    }

    /**
     * Add user to favorites
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ], [
            'user_id.required' => 'المستخدم مطلوب',
            'user_id.exists' => 'المستخدم غير موجود',
        ]);

        $user = $request->user();
        $targetUser = User::findOrFail($request->user_id);

        // Cannot favorite yourself
        if ($user->id === $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك إضافة نفسك للمفضلة',
            ], 422);
        }

        // Check if already favorited
        if ($user->favorites()->where('favorited_user_id', $targetUser->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم موجود في المفضلة مسبقاً',
            ], 422);
        }

        // Check favorites limit
        $maxFavorites = config('tayseer.max_favorites', 50);
        if ($user->favorites()->count() >= $maxFavorites) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن إضافة أكثر من {$maxFavorites} مستخدم للمفضلة",
            ], 422);
        }

        $favorite = $user->favorites()->create([
            'favorited_user_id' => $targetUser->id,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تمت الإضافة للمفضلة بنجاح',
            'favorite' => [
                'id' => $favorite->id,
                'user_id' => $targetUser->id,
            ],
        ], 201);
    }

    /**
     * Remove user from favorites
     */
    public function destroy(Request $request, int $userId): JsonResponse
    {
        $user = $request->user();

        $deleted = $user->favorites()->where('favorited_user_id', $userId)->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود في المفضلة',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت الإزالة من المفضلة بنجاح',
        ]);
    }
}
