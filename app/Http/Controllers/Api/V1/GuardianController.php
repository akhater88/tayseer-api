<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatRequestResource;
use App\Http\Resources\UserResource;
use App\Models\ChatRequest;
use App\Services\GuardianService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class GuardianController extends Controller
{
    public function __construct(
        protected GuardianService $guardianService,
        protected OtpService $otpService
    ) {}

    /**
     * Verify guardian invitation code (public)
     */
    public function verifyInvitation(Request $request): JsonResponse
    {
        $request->validate([
            'invitation_code' => 'required|string|size:8',
        ], [
            'invitation_code.required' => 'رمز الدعوة مطلوب',
            'invitation_code.size' => 'رمز الدعوة يجب أن يكون 8 أحرف',
        ]);

        $result = $this->guardianService->verifyInvitation($request->invitation_code);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Register as guardian (public)
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'invitation_code' => 'required|string|size:8',
            'full_name' => 'required|string|max:100',
            'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'otp_code' => 'required|string|size:6',
        ], [
            'invitation_code.required' => 'رمز الدعوة مطلوب',
            'full_name.required' => 'الاسم الكامل مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex' => 'صيغة رقم الهاتف غير صحيحة',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'otp_code.required' => 'رمز التحقق مطلوب',
        ]);

        // Verify OTP first
        $otpResult = $this->otpService->verify(
            $request->phone,
            $request->otp_code,
            'registration'
        );

        if (!$otpResult['success']) {
            return response()->json($otpResult, 422);
        }

        $result = $this->guardianService->registerGuardian(
            $request->invitation_code,
            $request->full_name,
            $request->phone,
            $request->password
        );

        return response()->json($result, $result['success'] ? 201 : 422);
    }

    /**
     * Get guardian dashboard (protected, guardian middleware)
     */
    public function dashboard(Request $request): JsonResponse
    {
        $result = $this->guardianService->getDashboard($request->user());

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Get chat requests to review (protected, guardian middleware)
     */
    public function chatRequests(Request $request): JsonResponse
    {
        $status = $request->query('status'); // pending, approved, rejected

        $requests = $this->guardianService->getChatRequests($request->user(), $status);

        return response()->json([
            'success' => true,
            'data' => ChatRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    /**
     * Get single chat request details (protected, guardian middleware)
     */
    public function showChatRequest(Request $request, ChatRequest $chatRequest): JsonResponse
    {
        $guardian = $request->user();

        // Verify guardian has access to this request
        $hasAccess = $guardian->wards()
            ->where('female_user_id', $chatRequest->receiver_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذا الطلب',
            ], 403);
        }

        $chatRequest->load(['requester.profile', 'requester.photos', 'match']);

        return response()->json([
            'success' => true,
            'data' => new ChatRequestResource($chatRequest),
            'suitor_profile' => new UserResource($chatRequest->requester),
        ]);
    }

    /**
     * Respond to chat request - approve or reject (protected, guardian middleware)
     */
    public function respondToChatRequest(Request $request, ChatRequest $chatRequest): JsonResponse
    {
        $request->validate([
            'decision' => 'required|string|in:approved,rejected',
            'rejection_reason' => 'nullable|string|max:500',
        ], [
            'decision.required' => 'القرار مطلوب',
            'decision.in' => 'القرار يجب أن يكون موافقة أو رفض',
        ]);

        $guardian = $request->user();

        if ($request->decision === 'approved') {
            $result = $this->guardianService->approveChatRequest($chatRequest, $guardian);
        } else {
            $result = $this->guardianService->rejectChatRequest(
                $chatRequest,
                $guardian,
                $request->rejection_reason
            );
        }

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get approved conversations (protected, guardian middleware)
     */
    public function approved(Request $request): JsonResponse
    {
        $requests = $this->guardianService->getChatRequests($request->user(), 'approved');

        return response()->json([
            'success' => true,
            'data' => ChatRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    /**
     * Revoke approval (end conversation) (protected, guardian middleware)
     */
    public function revokeApproval(Request $request, ChatRequest $chatRequest): JsonResponse
    {
        $result = $this->guardianService->revokeApproval($chatRequest, $request->user());

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
