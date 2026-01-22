<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\UserResource;
use App\Models\DeviceToken;
use App\Models\GuardianInvitation;
use App\Models\UserPhoto;
use App\Services\ContentFilterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile;

        // Update user fields (limited - cannot change phone, gender, is_convert)
        $userFields = ['username', 'email'];
        $userData = $request->only($userFields);
        if (!empty($userData)) {
            $user->update($userData);
        }

        // Update profile fields
        $profileFields = [
            'full_name', 'date_of_birth', 'nationality_id', 'country_id', 'city_id',
            'marital_status', 'number_of_children', 'number_of_wives',
            'height_cm', 'weight_kg', 'skin_color', 'body_type',
            'religious_level', 'prayer_level', 'smoking',
            'beard_type', 'hijab_type',
            'education_level', 'work_field_id', 'job_title',
            'about_me', 'partner_preferences',
        ];

        $profileData = $request->only($profileFields);

        // Filter gender-specific fields
        if ($user->isMale()) {
            unset($profileData['hijab_type']);
        } else {
            unset($profileData['beard_type']);
            unset($profileData['number_of_wives']);
        }

        if (!empty($profileData)) {
            $profile->update($profileData);
            $profile->updateCompletion();
        }

        $user->refresh()->load(['profile', 'primaryPhoto', 'guardian']);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ], [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'password.required' => 'كلمة المرور الجديدة مطلوبة',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور الحالية غير صحيحة',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);
    }

    /**
     * Delete account (soft delete)
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'كلمة المرور مطلوبة للتأكيد',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور غير صحيحة',
            ], 422);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الحساب بنجاح',
        ]);
    }

    /**
     * Upload photo
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5MB max
        ], [
            'photo.required' => 'الصورة مطلوبة',
            'photo.image' => 'الملف يجب أن يكون صورة',
            'photo.mimes' => 'صيغة الصورة يجب أن تكون jpg أو png',
            'photo.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت',
        ]);

        $user = $request->user();
        $maxPhotos = config('tayseer.max_photos', 5);

        // Check photo limit
        if ($user->photos()->count() >= $maxPhotos) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن إضافة أكثر من {$maxPhotos} صور",
            ], 422);
        }

        $file = $request->file('photo');

        // Generate unique filename
        $filename = uniqid('photo_') . '.' . $file->getClientOriginalExtension();
        $thumbnailFilename = 'thumb_' . $filename;

        // Store original
        $path = $file->storeAs('photos/' . $user->id, $filename, 'public');

        // Create and store thumbnail
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getPathname());
            $image->cover(300, 300);

            $thumbnailPath = 'photos/' . $user->id . '/' . $thumbnailFilename;
            Storage::disk('public')->put($thumbnailPath, $image->toJpeg(80));
        } catch (\Exception $e) {
            $thumbnailPath = null;
        }

        // Determine sort order
        $sortOrder = $user->photos()->max('sort_order') + 1;

        // Determine if this should be primary (first photo)
        $isPrimary = $user->photos()->count() === 0;

        // Create photo record
        $photo = $user->photos()->create([
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'is_primary' => $isPrimary,
            'is_approved' => false, // Pending moderation
            'sort_order' => $sortOrder,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع الصورة بنجاح',
            'photo' => new PhotoResource($photo),
        ], 201);
    }

    /**
     * Delete photo
     */
    public function deletePhoto(Request $request, UserPhoto $photo): JsonResponse
    {
        $user = $request->user();

        // Ensure photo belongs to user
        if ($photo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بهذا الإجراء',
            ], 403);
        }

        $wasPrimary = $photo->is_primary;

        // Delete files
        Storage::disk('public')->delete($photo->path);
        if ($photo->thumbnail_path) {
            Storage::disk('public')->delete($photo->thumbnail_path);
        }

        $photo->delete();

        // If deleted photo was primary, set another as primary
        if ($wasPrimary) {
            $newPrimary = $user->photos()->orderBy('sort_order')->first();
            if ($newPrimary) {
                $newPrimary->makePrimary();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصورة بنجاح',
        ]);
    }

    /**
     * Set photo as primary
     */
    public function setPrimaryPhoto(Request $request, UserPhoto $photo): JsonResponse
    {
        $user = $request->user();

        // Ensure photo belongs to user
        if ($photo->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح بهذا الإجراء',
            ], 403);
        }

        $photo->makePrimary();

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين الصورة الرئيسية بنجاح',
            'photo' => new PhotoResource($photo->fresh()),
        ]);
    }

    /**
     * Set guardian info (for females only)
     */
    public function setGuardian(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only females can set guardian
        if (!$user->isFemale()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإجراء مخصص للإناث فقط',
            ], 403);
        }

        // Convert females don't need guardian
        if ($user->isConvert()) {
            return response()->json([
                'success' => false,
                'message' => 'المسلمات الجديدات لا يحتجن ولي أمر',
            ], 422);
        }

        // Check if already has active guardian
        if ($user->hasGuardian()) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد ولي أمر مسجل مسبقاً',
            ], 422);
        }

        $request->validate([
            'guardian_name' => 'required|string|max:100',
            'guardian_phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/',
            'guardian_relationship' => 'required|string|in:father,brother,son,uncle,grandfather',
        ], [
            'guardian_name.required' => 'اسم ولي الأمر مطلوب',
            'guardian_phone.required' => 'رقم هاتف ولي الأمر مطلوب',
            'guardian_phone.regex' => 'صيغة رقم هاتف ولي الأمر غير صحيحة',
            'guardian_relationship.required' => 'صلة القرابة بولي الأمر مطلوبة',
        ]);

        // Invalidate any existing pending invitations
        $user->guardianInvitations()->whereIn('status', ['pending', 'sent'])->update(['status' => 'expired']);

        // Create new invitation
        $invitation = $user->guardianInvitations()->create([
            'guardian_name' => $request->guardian_name,
            'guardian_phone' => $request->guardian_phone,
            'relationship' => $request->guardian_relationship,
            'invitation_code' => GuardianInvitation::generateCode(),
            'status' => 'pending',
            'expires_at' => now()->addDays(config('tayseer.guardian.invitation_expiry_days', 30)),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ بيانات ولي الأمر بنجاح',
            'guardian_invitation' => [
                'id' => $invitation->id,
                'guardian_name' => $invitation->guardian_name,
                'relationship' => $invitation->relationship,
                'status' => $invitation->status,
            ],
        ], 201);
    }

    /**
     * Update guardian info
     */
    public function updateGuardian(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isFemale()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإجراء مخصص للإناث فقط',
            ], 403);
        }

        // Check if guardian already registered
        if ($user->hasGuardian()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل بيانات ولي الأمر بعد التسجيل',
            ], 422);
        }

        // Get pending invitation
        $invitation = $user->guardianInvitations()->whereIn('status', ['pending', 'sent'])->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد دعوة ولي أمر نشطة',
            ], 404);
        }

        $request->validate([
            'guardian_name' => 'sometimes|required|string|max:100',
            'guardian_phone' => 'sometimes|required|string|regex:/^\+?[0-9]{10,15}$/',
            'guardian_relationship' => 'sometimes|required|string|in:father,brother,son,uncle,grandfather',
        ]);

        $invitation->update($request->only(['guardian_name', 'guardian_phone', 'guardian_relationship']));

        // If phone changed, reset status and regenerate code
        if ($request->has('guardian_phone') && $invitation->isSent()) {
            $invitation->update([
                'status' => 'pending',
                'invitation_code' => GuardianInvitation::generateCode(),
                'sent_at' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات ولي الأمر بنجاح',
        ]);
    }

    /**
     * Remove guardian
     */
    public function removeGuardian(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isFemale()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإجراء مخصص للإناث فقط',
            ], 403);
        }

        // Cannot remove if guardian registered and has approved chats
        if ($user->hasGuardian()) {
            // Check for active approved chats
            $hasApprovedChats = $user->guardian->guardianUser
                ->whereHas('chatRequests', fn($q) => $q->approved())
                ->exists();

            if ($hasApprovedChats) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إزالة ولي الأمر لوجود محادثات موافق عليها',
                ], 422);
            }

            // Revoke guardian relationship
            $user->guardian->revoke();
        }

        // Expire all pending invitations
        $user->guardianInvitations()->whereIn('status', ['pending', 'sent'])->update(['status' => 'expired']);

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة ولي الأمر بنجاح',
        ]);
    }

    /**
     * Register device token for push notifications
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|string|in:ios,android,web',
        ]);

        $user = $request->user();

        // Deactivate old tokens for same platform
        DeviceToken::where('token', $request->token)->delete();

        // Create or update token
        $user->deviceTokens()->updateOrCreate(
            ['token' => $request->token],
            [
                'platform' => $request->platform,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الجهاز بنجاح',
        ]);
    }

    /**
     * Remove device token
     */
    public function removeDeviceToken(Request $request, string $token): JsonResponse
    {
        $user = $request->user();

        $deleted = $user->deviceTokens()->where('token', $token)->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'الجهاز غير موجود',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تسجيل الجهاز بنجاح',
        ]);
    }

    /**
     * Get notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم قراءة جميع الإشعارات',
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markNotificationRead(Request $request, string $notification): JsonResponse
    {
        $notification = $request->user()->notifications()->find($notification);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم قراءة الإشعار',
        ]);
    }
}
