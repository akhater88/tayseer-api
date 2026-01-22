<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function __construct(
        protected InfobipService $smsService
    ) {}

    /**
     * Generate and send OTP
     */
    public function send(string $phone, string $purpose): array
    {
        // Check cooldown
        $lastOtp = OtpCode::forPhone($phone)
            ->forPurpose($purpose)
            ->latest('created_at')
            ->first();

        if ($lastOtp) {
            $cooldownMinutes = config('tayseer.otp.cooldown_minutes', 1);
            $cooldownEnds = $lastOtp->created_at->addMinutes($cooldownMinutes);

            if ($cooldownEnds->isFuture()) {
                return [
                    'success' => false,
                    'message' => 'Please wait before requesting another OTP',
                    'retry_after' => $cooldownEnds->diffInSeconds(now()),
                ];
            }
        }

        // Generate OTP
        $otp = OtpCode::generate($phone, $purpose);

        // Send SMS
        $message = $this->getOtpMessage($otp->code, $purpose);
        $sent = $this->smsService->send($phone, $message);

        if (!$sent) {
            Log::error('Failed to send OTP SMS', [
                'phone' => $phone,
                'purpose' => $purpose,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP sent successfully',
            'expires_in' => config('tayseer.otp.expiry_minutes', 5) * 60,
        ];
    }

    /**
     * Verify OTP
     */
    public function verify(string $phone, string $code, string $purpose): array
    {
        $otp = OtpCode::findValid($phone, $purpose);

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'OTP expired or invalid. Please request a new one.',
            ];
        }

        if ($otp->verify($code)) {
            return [
                'success' => true,
                'message' => 'OTP verified successfully',
            ];
        }

        $remainingAttempts = config('tayseer.otp.max_attempts', 3) - $otp->attempts;

        return [
            'success' => false,
            'message' => $remainingAttempts > 0
                ? "Invalid OTP. {$remainingAttempts} attempts remaining."
                : 'Too many failed attempts. Please request a new OTP.',
        ];
    }

    /**
     * Check if phone has valid OTP (for registration flow)
     */
    public function hasValidOtp(string $phone, string $purpose): bool
    {
        return OtpCode::forPhone($phone)
            ->forPurpose($purpose)
            ->where('is_used', true)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();
    }

    /**
     * Get OTP message based on purpose
     */
    protected function getOtpMessage(string $code, string $purpose): string
    {
        $appName = config('app.name');

        return match ($purpose) {
            'registration' => "رمز التحقق الخاص بك في {$appName}: {$code}\nصالح لمدة 5 دقائق.",
            'login' => "رمز الدخول الخاص بك في {$appName}: {$code}\nلا تشاركه مع أحد.",
            'password_reset' => "رمز إعادة تعيين كلمة المرور في {$appName}: {$code}",
            default => "رمز التحقق الخاص بك: {$code}",
        };
    }
}
