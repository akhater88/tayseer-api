<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    /**
     * Get list of blocked users
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->query('per_page', 20), 50);

        $blocks = $user->blocks()
            ->with(['blocked.profile', 'blocked.primaryPhoto'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = $blocks->through(function ($block) {
            return [
                'id' => $block->id,
                'user' => new UserResource($block->blocked),
                'reason' => $block->reason,
                'blocked_at' => $block->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $blocks->currentPage(),
                'last_page' => $blocks->lastPage(),
                'per_page' => $blocks->perPage(),
                'total' => $blocks->total(),
            ],
        ]);
    }

    /**
     * Block a user
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ], [
            'user_id.required' => 'المستخدم مطلوب',
            'user_id.exists' => 'المستخدم غير موجود',
        ]);

        $user = $request->user();
        $targetUser = User::findOrFail($request->user_id);

        // Cannot block yourself
        if ($user->id === $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك حظر نفسك',
            ], 422);
        }

        // Check if already blocked
        if ($user->hasBlocked($targetUser)) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم محظور مسبقاً',
            ], 422);
        }

        // Create block
        $block = $user->blocks()->create([
            'blocked_id' => $targetUser->id,
            'reason' => $request->reason,
            'created_at' => now(),
        ]);

        // Remove from favorites if exists
        $user->favorites()->where('favorited_user_id', $targetUser->id)->delete();

        // Withdraw any pending interests
        $user->sentInterests()
            ->where('receiver_id', $targetUser->id)
            ->where('status', 'pending')
            ->update(['status' => 'withdrawn']);

        $user->receivedInterests()
            ->where('sender_id', $targetUser->id)
            ->where('status', 'pending')
            ->update(['status' => 'declined', 'responded_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'تم حظر المستخدم بنجاح',
            'block' => [
                'id' => $block->id,
                'user_id' => $targetUser->id,
            ],
        ], 201);
    }

    /**
     * Unblock a user
     */
    public function destroy(Request $request, int $userId): JsonResponse
    {
        $user = $request->user();

        $deleted = $user->blocks()->where('blocked_id', $userId)->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير محظور',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء حظر المستخدم بنجاح',
        ]);
    }
}
