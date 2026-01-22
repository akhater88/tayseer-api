<?php

namespace App\Services;

use App\Enums\ChatRequestStatus;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\ChatRequest;
use App\Models\Guardian;
use App\Models\GuardianInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuardianService
{
    public function __construct(
        protected InfobipService $infobipService
    ) {}

    /**
     * Verify guardian invitation code
     */
    public function verifyInvitation(string $code): array
    {
        $invitation = GuardianInvitation::findByCode($code);

        if (!$invitation) {
            return [
                'success' => false,
                'message' => 'رمز الدعوة غير صالح أو منتهي الصلاحية',
            ];
        }

        $femaleUser = $invitation->femaleUser;

        return [
            'success' => true,
            'valid' => true,
            'female_user' => [
                'name' => $femaleUser->username,
                'age' => $femaleUser->getAge(),
                'city' => $femaleUser->profile?->city?->name_ar,
            ],
            'relationship' => $invitation->relationship->value,
            'relationship_label' => $invitation->relationship->labelAr(),
        ];
    }

    /**
     * Register guardian user
     */
    public function registerGuardian(
        string $invitationCode,
        string $fullName,
        string $phone,
        string $password
    ): array {
        $invitation = GuardianInvitation::findByCode($invitationCode);

        if (!$invitation) {
            return [
                'success' => false,
                'message' => 'رمز الدعوة غير صالح أو منتهي الصلاحية',
            ];
        }

        // Check if phone already registered
        if (User::where('phone', $phone)->exists()) {
            return [
                'success' => false,
                'message' => 'رقم الهاتف مسجل مسبقاً',
            ];
        }

        return DB::transaction(function () use ($invitation, $fullName, $phone, $password) {
            // Create guardian user
            $guardianUser = User::create([
                'username' => 'guardian_' . uniqid(),
                'phone' => $phone,
                'password' => Hash::make($password),
                'gender' => 'male', // Guardians are typically male
                'user_type' => UserType::Guardian,
                'status' => UserStatus::Active,
                'phone_verified_at' => now(),
            ]);

            // Create minimal profile for guardian
            $guardianUser->profile()->create([
                'full_name' => $fullName,
            ]);

            // Create guardian relationship
            Guardian::create([
                'guardian_user_id' => $guardianUser->id,
                'female_user_id' => $invitation->female_user_id,
                'relationship' => $invitation->relationship,
                'status' => 'active',
                'invited_at' => $invitation->created_at,
                'registered_at' => now(),
            ]);

            // Mark invitation as accepted
            $invitation->markAsAccepted();

            // Get pending chat requests count
            $pendingRequestsCount = ChatRequest::where('receiver_id', $invitation->female_user_id)
                ->where('status', ChatRequestStatus::PendingGuardian)
                ->count();

            // Generate token
            $token = $guardianUser->createToken('guardian-token')->plainTextToken;

            return [
                'success' => true,
                'message' => 'تم التسجيل كولي أمر بنجاح',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $guardianUser->id,
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'user_type' => 'guardian',
                ],
                'ward' => [
                    'name' => $invitation->femaleUser->username,
                    'pending_requests_count' => $pendingRequestsCount,
                ],
            ];
        });
    }

    /**
     * Send guardian invitation SMS
     */
    public function sendInvitation(GuardianInvitation $invitation): array
    {
        if ($invitation->isExpired()) {
            return [
                'success' => false,
                'message' => 'انتهت صلاحية الدعوة',
            ];
        }

        // Send SMS
        $result = $this->infobipService->sendGuardianInvitation(
            $invitation->guardian_phone,
            $invitation->guardian_name,
            $invitation->femaleUser->username,
            $invitation->invitation_code
        );

        if ($result['success']) {
            $invitation->markAsSent();
        }

        return $result;
    }

    /**
     * Get guardian dashboard data
     */
    public function getDashboard(User $guardian): array
    {
        // Get ward (female user)
        $guardianship = Guardian::where('guardian_user_id', $guardian->id)
            ->where('status', 'active')
            ->with('femaleUser.profile')
            ->first();

        if (!$guardianship) {
            return [
                'success' => false,
                'message' => 'لا يوجد موكلة مرتبطة بحسابك',
            ];
        }

        $ward = $guardianship->femaleUser;

        // Get statistics
        $pendingRequests = ChatRequest::where('receiver_id', $ward->id)
            ->where('status', ChatRequestStatus::PendingGuardian)
            ->count();

        $approvedConversations = ChatRequest::where('receiver_id', $ward->id)
            ->where('status', ChatRequestStatus::Approved)
            ->count();

        $rejectedRequests = ChatRequest::where('receiver_id', $ward->id)
            ->where('status', ChatRequestStatus::Rejected)
            ->where('guardian_id', $guardian->id)
            ->count();

        return [
            'success' => true,
            'ward' => [
                'id' => $ward->id,
                'name' => $ward->username,
                'age' => $ward->getAge(),
                'profile_completion' => $ward->profile?->profile_completion ?? 0,
            ],
            'stats' => [
                'pending_requests' => $pendingRequests,
                'approved_conversations' => $approvedConversations,
                'rejected_requests' => $rejectedRequests,
            ],
        ];
    }

    /**
     * Get chat requests for guardian to review
     */
    public function getChatRequests(User $guardian, ?string $status = null)
    {
        // Get ward
        $guardianship = Guardian::where('guardian_user_id', $guardian->id)
            ->where('status', 'active')
            ->first();

        if (!$guardianship) {
            return collect();
        }

        $query = ChatRequest::where('receiver_id', $guardianship->female_user_id)
            ->with(['requester.profile', 'requester.primaryPhoto', 'match']);

        if ($status === 'pending') {
            $query->where('status', ChatRequestStatus::PendingGuardian);
        } elseif ($status === 'approved') {
            $query->where('status', ChatRequestStatus::Approved);
        } elseif ($status === 'rejected') {
            $query->where('status', ChatRequestStatus::Rejected)
                ->where('guardian_id', $guardian->id);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Approve chat request
     */
    public function approveChatRequest(ChatRequest $chatRequest, User $guardian): array
    {
        // Verify guardian owns this request
        $guardianship = Guardian::where('guardian_user_id', $guardian->id)
            ->where('female_user_id', $chatRequest->receiver_id)
            ->where('status', 'active')
            ->first();

        if (!$guardianship) {
            return [
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء',
            ];
        }

        if (!$chatRequest->isPendingGuardian()) {
            return [
                'success' => false,
                'message' => 'تم الرد على هذا الطلب مسبقاً',
            ];
        }

        $chatRequest->update([
            'status' => ChatRequestStatus::Approved,
            'guardian_id' => $guardian->id,
            'guardian_reviewed_at' => now(),
            'guardian_decision' => 'approved',
        ]);

        // TODO: Create Firebase conversation here
        // $conversationId = app(FirebaseService::class)->createConversation($chatRequest);
        // $chatRequest->setFirebaseConversation($conversationId);

        return [
            'success' => true,
            'message' => 'تمت الموافقة على طلب المحادثة',
            'chat_request' => $chatRequest->fresh(),
        ];
    }

    /**
     * Reject chat request
     */
    public function rejectChatRequest(ChatRequest $chatRequest, User $guardian, ?string $reason = null): array
    {
        // Verify guardian owns this request
        $guardianship = Guardian::where('guardian_user_id', $guardian->id)
            ->where('female_user_id', $chatRequest->receiver_id)
            ->where('status', 'active')
            ->first();

        if (!$guardianship) {
            return [
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء',
            ];
        }

        if (!$chatRequest->isPendingGuardian()) {
            return [
                'success' => false,
                'message' => 'تم الرد على هذا الطلب مسبقاً',
            ];
        }

        $chatRequest->rejectByGuardian($reason);

        return [
            'success' => true,
            'message' => 'تم رفض طلب المحادثة',
        ];
    }

    /**
     * Revoke approval (end conversation)
     */
    public function revokeApproval(ChatRequest $chatRequest, User $guardian): array
    {
        // Verify guardian owns this request
        $guardianship = Guardian::where('guardian_user_id', $guardian->id)
            ->where('female_user_id', $chatRequest->receiver_id)
            ->where('status', 'active')
            ->first();

        if (!$guardianship) {
            return [
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء',
            ];
        }

        if (!$chatRequest->isApproved()) {
            return [
                'success' => false,
                'message' => 'هذه المحادثة غير موافق عليها',
            ];
        }

        $chatRequest->update([
            'status' => ChatRequestStatus::Rejected,
            'guardian_decision' => 'revoked',
            'guardian_rejection_reason' => 'تم إلغاء الموافقة من قبل ولي الأمر',
        ]);

        // TODO: Disable Firebase conversation here
        // app(FirebaseService::class)->disableConversation($chatRequest->firebase_conversation_id);

        return [
            'success' => true,
            'message' => 'تم إلغاء الموافقة على المحادثة',
        ];
    }
}
