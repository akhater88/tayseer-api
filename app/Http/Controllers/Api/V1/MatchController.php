<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ChatRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatRequestResource;
use App\Http\Resources\MatchResource;
use App\Http\Resources\UserResource;
use App\Models\ChatRequest;
use App\Models\Guardian;
use App\Models\GuardianInvitation;
use App\Models\Match as MatchModel;
use App\Services\GuardianService;
use App\Services\InfobipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function __construct(
        protected GuardianService $guardianService,
        protected InfobipService $infobipService
    ) {}

    /**
     * Get all matches for the user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->query('per_page', 20), 50);

        $matches = MatchModel::query()
            ->with(['user1.profile', 'user1.primaryPhoto', 'user2.profile', 'user2.primaryPhoto', 'chatRequest'])
            ->forUser($user)
            ->active()
            ->orderBy('matched_at', 'desc')
            ->paginate($perPage);

        // Transform with other user info
        $data = $matches->through(function ($match) use ($user) {
            return [
                'match' => new MatchResource($match),
                'other_user' => new UserResource($match->getOtherUser($user)),
                'chat_request_status' => $match->chatRequest?->status?->value,
                'can_request_chat' => !$match->hasChatRequest(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $matches->currentPage(),
                'last_page' => $matches->lastPage(),
                'per_page' => $matches->perPage(),
                'total' => $matches->total(),
            ],
        ]);
    }

    /**
     * Get single match details
     */
    public function show(Request $request, MatchModel $match): JsonResponse
    {
        $user = $request->user();

        // Verify user is part of match
        if (!$match->hasUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذا التوافق',
            ], 403);
        }

        $match->load(['user1.profile', 'user1.photos', 'user2.profile', 'user2.photos', 'chatRequest']);

        return response()->json([
            'success' => true,
            'data' => [
                'match' => new MatchResource($match),
                'other_user' => new UserResource($match->getOtherUser($user)),
                'chat_request' => $match->chatRequest ? new ChatRequestResource($match->chatRequest) : null,
            ],
        ]);
    }

    /**
     * Request to start chat
     */
    public function requestChat(Request $request, MatchModel $match): JsonResponse
    {
        $user = $request->user();

        // Verify user is part of match
        if (!$match->hasUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء',
            ], 403);
        }

        // Check if chat request already exists
        if ($match->hasChatRequest()) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد طلب محادثة مسبق',
            ], 422);
        }

        $otherUser = $match->getOtherUser($user);

        return DB::transaction(function () use ($match, $user, $otherUser) {
            // Determine initial status based on receiver
            if ($otherUser->needsGuardianApproval()) {
                // Non-convert female - needs to accept first, then guardian approval
                $status = ChatRequestStatus::PendingFemale;
                $guardianId = null;
                $message = 'تم إرسال طلب المحادثة، في انتظار موافقة الطرف الآخر';

                // Check if guardian exists
                if ($otherUser->hasGuardian()) {
                    // Guardian registered, will be notified after female accepts
                    $guardianId = $otherUser->guardian->guardian_user_id;
                } else {
                    // Guardian not registered yet - check for pending invitation
                    $pendingInvitation = $otherUser->guardianInvitations()
                        ->whereIn('status', ['pending', 'sent'])
                        ->first();

                    if ($pendingInvitation && !$pendingInvitation->isSent()) {
                        // Send invitation SMS to guardian
                        $this->guardianService->sendInvitation($pendingInvitation);
                    }
                }
            } elseif ($otherUser->isFemale() && $otherUser->isConvert()) {
                // Convert female - direct approval pending
                $status = ChatRequestStatus::PendingFemale;
                $guardianId = null;
                $message = 'تم إرسال طلب المحادثة، في انتظار موافقة الطرف الآخر';
            } else {
                // Male receiver - direct approval pending
                $status = ChatRequestStatus::PendingFemale; // Using same status for simplicity
                $guardianId = null;
                $message = 'تم إرسال طلب المحادثة، في انتظار موافقة الطرف الآخر';
            }

            $chatRequest = ChatRequest::create([
                'match_id' => $match->id,
                'requester_id' => $user->id,
                'receiver_id' => $otherUser->id,
                'status' => $status,
                'guardian_id' => $guardianId,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'chat_request' => new ChatRequestResource($chatRequest),
            ], 201);
        });
    }

    /**
     * Respond to chat request
     */
    public function respondToChatRequest(Request $request, MatchModel $match): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:accept,reject',
            'rejection_reason' => 'nullable|string|max:500',
        ], [
            'action.required' => 'الإجراء مطلوب',
            'action.in' => 'الإجراء يجب أن يكون قبول أو رفض',
        ]);

        $user = $request->user();

        // Verify user is part of match
        if (!$match->hasUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء',
            ], 403);
        }

        $chatRequest = $match->chatRequest;

        if (!$chatRequest) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد طلب محادثة',
            ], 404);
        }

        // Verify user is the receiver
        if ($chatRequest->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'أنت غير مصرح بالرد على هذا الطلب',
            ], 403);
        }

        // Check if already responded
        if (!$chatRequest->isPendingFemale()) {
            return response()->json([
                'success' => false,
                'message' => 'تم الرد على هذا الطلب مسبقاً',
            ], 422);
        }

        if ($request->action === 'accept') {
            // Female accepts
            if ($user->needsGuardianApproval()) {
                // Non-convert female - move to pending guardian
                if (!$user->hasGuardian()) {
                    // Check for pending invitation and send if not sent
                    $invitation = $user->guardianInvitations()->whereIn('status', ['pending', 'sent'])->first();

                    if ($invitation && !$invitation->isSent()) {
                        $this->guardianService->sendInvitation($invitation);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'تم قبول الطلب. يرجى انتظار تسجيل ولي أمرك',
                        'chat_request' => new ChatRequestResource($chatRequest->fresh()),
                        'needs_guardian' => true,
                        'guardian_registered' => false,
                    ]);
                }

                $chatRequest->approveByFemale();

                return response()->json([
                    'success' => true,
                    'message' => 'تم قبول الطلب. في انتظار موافقة ولي الأمر',
                    'chat_request' => new ChatRequestResource($chatRequest->fresh()),
                    'needs_guardian' => true,
                    'guardian_registered' => true,
                ]);
            } else {
                // Convert female or male - direct approval
                $chatRequest->approve();

                // TODO: Create Firebase conversation
                // $conversationId = app(FirebaseService::class)->createConversation($chatRequest);
                // $chatRequest->setFirebaseConversation($conversationId);

                return response()->json([
                    'success' => true,
                    'message' => 'تمت الموافقة على المحادثة',
                    'chat_request' => new ChatRequestResource($chatRequest->fresh()),
                    'firebase_conversation_id' => $chatRequest->firebase_conversation_id,
                ]);
            }
        } else {
            // Reject
            $chatRequest->rejectByFemale();

            return response()->json([
                'success' => true,
                'message' => 'تم رفض طلب المحادثة',
            ]);
        }
    }

    /**
     * Get all approved conversations (with Firebase IDs)
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->query('per_page', 20), 50);

        $conversations = ChatRequest::query()
            ->with(['match', 'requester.profile', 'requester.primaryPhoto', 'receiver.profile', 'receiver.primaryPhoto'])
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->where('status', ChatRequestStatus::Approved)
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        $data = $conversations->through(function ($chatRequest) use ($user) {
            $otherUser = $chatRequest->requester_id === $user->id
                ? $chatRequest->receiver
                : $chatRequest->requester;

            return [
                'conversation_id' => $chatRequest->id,
                'match_id' => $chatRequest->match_id,
                'firebase_conversation_id' => $chatRequest->firebase_conversation_id,
                'other_user' => new UserResource($otherUser),
                'approved_at' => $chatRequest->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Get single conversation details
     */
    public function conversation(Request $request, int $conversation): JsonResponse
    {
        $user = $request->user();

        $chatRequest = ChatRequest::query()
            ->with(['match', 'requester.profile', 'requester.photos', 'receiver.profile', 'receiver.photos'])
            ->where('id', $conversation)
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->where('status', ChatRequestStatus::Approved)
            ->first();

        if (!$chatRequest) {
            return response()->json([
                'success' => false,
                'message' => 'المحادثة غير موجودة',
            ], 404);
        }

        $otherUser = $chatRequest->requester_id === $user->id
            ? $chatRequest->receiver
            : $chatRequest->requester;

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $chatRequest->id,
                'match_id' => $chatRequest->match_id,
                'firebase_conversation_id' => $chatRequest->firebase_conversation_id,
                'other_user' => new UserResource($otherUser),
                'approved_at' => $chatRequest->updated_at->toIso8601String(),
            ],
        ]);
    }
}
