<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InfobipService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $senderId;

    public function __construct()
    {
        $this->baseUrl = config('services.infobip.base_url');
        $this->apiKey = config('services.infobip.api_key');
        $this->senderId = config('services.infobip.sender_id');
    }

    /**
     * Send SMS message
     */
    public function send(string $phone, string $message): bool
    {
        // In development, log instead of sending
        if (app()->environment('local', 'testing')) {
            Log::info('SMS would be sent', [
                'phone' => $phone,
                'message' => $message,
            ]);
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/sms/2/text/advanced', [
                'messages' => [
                    [
                        'destinations' => [
                            ['to' => $this->normalizePhone($phone)],
                        ],
                        'from' => $this->senderId,
                        'text' => $message,
                    ],
                ],
            ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('SMS failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('SMS exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send guardian invitation SMS
     */
    public function sendGuardianInvitation(
        string $phone,
        string $guardianName,
        string $femaleName,
        string $invitationCode
    ): bool {
        $appName = config('app.name');
        $appUrl = config('app.url');

        $message = <<<MSG
        السلام عليكم {$guardianName}

        تدعوك {$femaleName} للموافقة على طلبات الزواج الخاصة بها في تطبيق {$appName}.

        رمز الدعوة: {$invitationCode}
        الرابط: {$appUrl}/guardian/{$invitationCode}

        بارك الله فيكم
        MSG;

        return $this->send($phone, $message);
    }

    /**
     * Normalize phone number to international format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove any non-digit characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // If doesn't start with +, assume it needs country code
        if (!str_starts_with($phone, '+')) {
            // If starts with 0, remove it and add Saudi code
            if (str_starts_with($phone, '0')) {
                $phone = '966' . substr($phone, 1);
            }

            // Add + if not present
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }
        }

        return $phone;
    }
}
