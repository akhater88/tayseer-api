<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Send OTP to phone number
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->send(
            $request->phone,
            $request->purpose ?? 'registration'
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->verify(
            $request->phone,
            $request->code,
            $request->purpose ?? 'registration'
        );

        if ($result['success']) {
            // Generate temp token for registration
            $tempToken = encrypt([
                'phone' => $request->phone,
                'purpose' => $request->purpose ?? 'registration',
                'verified_at' => now()->timestamp,
            ]);

            $result['temp_token'] = $tempToken;
        }

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Register new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Verify temp token
        try {
            $tokenData = decrypt($request->header('X-Temp-Token'));

            // Check token validity (30 minutes)
            if (now()->timestamp - $tokenData['verified_at'] > 1800) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please verify your phone again.',
                ], 422);
            }

            if ($tokenData['phone'] !== $request->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number mismatch.',
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token. Please verify your phone again.',
            ], 422);
        }

        // Create user (slug is auto-generated in model boot)
        $user = User::create([
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'user_type' => UserType::Member,
            'status' => UserStatus::Active,
            'is_convert' => $request->is_convert ?? false,
            'phone_verified_at' => now(),
        ]);

        // Create profile with all fields
        $profileData = [
            // Private
            'full_name' => $request->full_name,

            // Demographics
            'date_of_birth' => $request->date_of_birth,
            'nationality_id' => $request->nationality_id,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,

            // Marital
            'marital_status' => $request->marital_status,
            'number_of_children' => $request->number_of_children ?? 0,

            // Physical
            'height_cm' => $request->height_cm,
            'weight_kg' => $request->weight_kg,
            'skin_color' => $request->skin_color,
            'body_type' => $request->body_type,

            // Religious
            'religious_level' => $request->religious_level,
            'prayer_level' => $request->prayer_level,
            'smoking' => $request->smoking,

            // Career
            'education_level' => $request->education_level,
            'work_field_id' => $request->work_field_id,
            'job_title' => $request->job_title,

            // Bio
            'about_me' => $request->about_me,
            'partner_preferences' => $request->partner_preferences,
        ];

        // Gender-specific fields
        if ($user->isMale()) {
            $profileData['beard_type'] = $request->beard_type;
            $profileData['number_of_wives'] = $request->number_of_wives ?? 0;
        } else {
            $profileData['hijab_type'] = $request->hijab_type;
        }

        $user->profile()->create($profileData);

        // Calculate profile completion
        $user->profile->updateCompletion();

        // Create guardian info if provided (for females)
        if ($user->isFemale() && !$user->isConvert() && $request->guardian_name) {
            $user->guardianInvitations()->create([
                'guardian_name' => $request->guardian_name,
                'guardian_phone' => $request->guardian_phone,
                'relationship' => $request->guardian_relationship,
                'invitation_code' => \App\Models\GuardianInvitation::generateCode(),
                'status' => 'pending',
                'expires_at' => now()->addDays(30),
            ]);
        }

        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم التسجيل بنجاح',
            'user' => new UserResource($user->load('profile')),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login with phone and password
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة',
            ], 401);
        }

        if ($user->status === UserStatus::Banned) {
            return response()->json([
                'success' => false,
                'message' => 'تم حظر هذا الحساب',
            ], 403);
        }

        if ($user->status === UserStatus::Suspended) {
            return response()->json([
                'success' => false,
                'message' => 'تم إيقاف هذا الحساب مؤقتاً',
            ], 403);
        }

        // Update last online
        $user->updateLastOnline();

        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => new UserResource($user->load('profile')),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Login with OTP
     */
    public function loginWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $result = $this->otpService->verify($request->phone, $request->code, 'login');

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على حساب بهذا الرقم',
            ], 404);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحساب غير نشط',
            ], 403);
        }

        $user->updateLastOnline();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => new UserResource($user->load('profile')),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'primaryPhoto', 'guardian']);
        $user->updateLastOnline();

        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Check username availability
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|min:4|max:15',
        ]);

        $username = strtolower($request->username);

        // Validation rules
        $errors = [];

        // Check format: alphanumeric + dots + underscores
        if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
            $errors[] = 'يجب أن يحتوي اسم المستخدم على حروف وأرقام ونقاط وشرطات سفلية فقط';
        }

        // Cannot start or end with dot
        if (str_starts_with($username, '.') || str_ends_with($username, '.')) {
            $errors[] = 'لا يمكن أن يبدأ أو ينتهي اسم المستخدم بنقطة';
        }

        // No consecutive dots
        if (str_contains($username, '..')) {
            $errors[] = 'لا يمكن استخدام نقاط متتالية';
        }

        // Check profanity (Arabic + English)
        $profanityWords = [
            // English
            'fuck', 'shit', 'ass', 'dick', 'bitch', 'porn', 'sex', 'xxx',
            // Arabic transliteration
            'sharmouta', 'sharmota', 'kalb', 'khanzeir', 'manyak', 'kuss', 'air',
        ];

        foreach ($profanityWords as $word) {
            if (stripos($username, $word) !== false) {
                $errors[] = 'اسم المستخدم يحتوي على كلمات غير لائقة';
                break;
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => $errors[0],
                'errors' => $errors,
            ], 422);
        }

        // Check if already taken
        $exists = User::where('username', $username)->exists();

        if ($exists) {
            return response()->json([
                'success' => true,
                'available' => false,
                'message' => 'اسم المستخدم مستخدم مسبقاً',
            ]);
        }

        return response()->json([
            'success' => true,
            'available' => true,
            'message' => 'اسم المستخدم متاح',
        ]);
    }
}
