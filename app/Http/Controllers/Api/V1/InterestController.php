<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InterestResource;
use App\Http\Resources\MatchResource;
use App\Models\Interest;
use App\Models\User;
use App\Services\MatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    public function __construct(
        protected MatchingService $matchingService
    ) {}

    /**
     * Send interest to another user
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'nullable|string|max:200',
        ], [
            'receiver_id.required' => 'المستخدم المستهدف مطلوب',
            'receiver_id.exists' => 'المستخدم غير موجود',
            'message.max' => 'الرسالة يجب ألا تتجاوز 200 حرف',
        ]);

        $sender = $request->user();
        $receiver = User::findOrFail($request->receiver_id);

        $result = $this->matchingService->sendInterest($sender, $receiver, $request->message);

        $statusCode = $result['success'] ? 201 : 422;

        if ($result['success']) {
            $result['interest'] = new InterestResource($result['interest']);
        }

        return response()->json($result, $statusCode);
    }

    /**
     * Get sent interests
     */
    public function sent(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->query('per_page', 20), 50);

        $interests = $user->sentInterests()
            ->with(['receiver.profile', 'receiver.primaryPhoto'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => InterestResource::collection($interests),
            'meta' => [
                'current_page' => $interests->currentPage(),
                'last_page' => $interests->lastPage(),
                'per_page' => $interests->perPage(),
                'total' => $interests->total(),
            ],
        ]);
    }

    /**
     * Get received interests
     */
    public function received(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->query('per_page', 20), 50);
        $status = $request->query('status'); // pending, accepted, declined

        $query = $user->receivedInterests()
            ->with(['sender.profile', 'sender.primaryPhoto']);

        if ($status) {
            $query->where('status', $status);
        }

        $interests = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => InterestResource::collection($interests),
            'meta' => [
                'current_page' => $interests->currentPage(),
                'last_page' => $interests->lastPage(),
                'per_page' => $interests->perPage(),
                'total' => $interests->total(),
            ],
        ]);
    }

    /**
     * Respond to an interest (accept or decline)
     */
    public function respond(Request $request, Interest $interest): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:accept,decline',
        ], [
            'action.required' => 'الإجراء مطلوب',
            'action.in' => 'الإجراء يجب أن يكون قبول أو رفض',
        ]);

        $user = $request->user();

        if ($request->action === 'accept') {
            $result = $this->matchingService->acceptInterest($interest, $user);
        } else {
            $result = $this->matchingService->declineInterest($interest, $user);
        }

        $statusCode = $result['success'] ? 200 : 422;

        if ($result['success']) {
            $result['interest'] = new InterestResource($result['interest']);
            if (isset($result['match'])) {
                $result['match'] = new MatchResource($result['match']);
            }
        }

        return response()->json($result, $statusCode);
    }

    /**
     * Withdraw a sent interest
     */
    public function destroy(Request $request, Interest $interest): JsonResponse
    {
        $user = $request->user();

        $result = $this->matchingService->withdrawInterest($interest, $user);

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
