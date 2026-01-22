<?php

namespace App\Services;

class ContentFilterService
{
    /**
     * Check if text contains contact information (phone, email, URL, social media)
     */
    public static function containsContactInfo(string $text): bool
    {
        // Pattern for phone numbers (international format)
        $phonePattern = '/(\+?\d{10,})/';

        // Pattern for emails
        $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';

        // Pattern for URLs (with or without protocol)
        $urlPattern = '/(https?:\/\/)?[\w.-]+\.[a-z]{2,}(\/\S*)?/i';

        // Pattern for social media handles
        $socialPattern = '/@[\w]+/';

        // Arabic keywords for social media and messaging apps
        $arabicKeywords = [
            'واتساب', 'واتس', 'واتسآب', 'whatsapp',
            'انستقرام', 'انستا', 'instagram', 'insta',
            'سناب', 'سنابشات', 'snapchat', 'snap',
            'تلجرام', 'تليجرام', 'telegram',
            'فيسبوك', 'فيس', 'facebook', 'fb',
            'تويتر', 'twitter', 'تيك توك', 'tiktok',
            'يوتيوب', 'youtube',
            'رقمي', 'جوالي', 'موبايلي', 'تواصل معي',
        ];

        // Check for phone numbers
        if (preg_match($phonePattern, $text)) {
            return true;
        }

        // Check for emails
        if (preg_match($emailPattern, $text)) {
            return true;
        }

        // Check for URLs
        if (preg_match($urlPattern, $text)) {
            return true;
        }

        // Check for social media handles
        if (preg_match($socialPattern, $text)) {
            return true;
        }

        // Check for Arabic keywords
        $textLower = mb_strtolower($text);
        foreach ($arabicKeywords as $keyword) {
            if (mb_stripos($textLower, mb_strtolower($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter/sanitize text by removing contact information
     */
    public static function filterContactInfo(string $text): string
    {
        // Remove phone numbers
        $text = preg_replace('/(\+?\d{10,})/', '[رقم محذوف]', $text);

        // Remove emails
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[بريد محذوف]', $text);

        // Remove URLs
        $text = preg_replace('/(https?:\/\/)?[\w.-]+\.[a-z]{2,}(\/\S*)?/i', '[رابط محذوف]', $text);

        // Remove social media handles
        $text = preg_replace('/@[\w]+/', '[حساب محذوف]', $text);

        return $text;
    }

    /**
     * Check if text contains profanity (Arabic + English)
     */
    public static function containsProfanity(string $text): bool
    {
        $profanityWords = [
            // English profanity
            'fuck', 'shit', 'ass', 'dick', 'bitch', 'porn', 'sex', 'xxx', 'pussy', 'cock',
            'whore', 'slut', 'bastard', 'damn', 'crap', 'piss',

            // Arabic profanity (transliterated)
            'sharmouta', 'sharmota', 'kalb', 'khanzeir', 'manyak', 'kuss', 'air',
            'ibn el kalb', 'ya kalb', 'ahbal', 'ghabi',

            // Arabic profanity (actual Arabic words)
            'شرموطة', 'كلب', 'خنزير', 'منيك', 'كس', 'عير', 'ابن الكلب',
            'أحمق', 'غبي', 'حمار', 'زبالة', 'قحبة',
        ];

        $textLower = mb_strtolower($text);
        foreach ($profanityWords as $word) {
            if (mb_stripos($textLower, mb_strtolower($word)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate bio/about_me text
     * Returns array with 'valid' boolean and 'errors' array
     */
    public static function validateBioText(string $text, int $minLength = 50, int $maxLength = 500): array
    {
        $errors = [];

        // Check length
        $length = mb_strlen($text);
        if ($length < $minLength) {
            $errors[] = "النص قصير جداً (الحد الأدنى {$minLength} حرف)";
        }
        if ($length > $maxLength) {
            $errors[] = "النص طويل جداً (الحد الأقصى {$maxLength} حرف)";
        }

        // Check for contact info
        if (self::containsContactInfo($text)) {
            $errors[] = 'لا يمكن إضافة معلومات التواصل';
        }

        // Check for profanity
        if (self::containsProfanity($text)) {
            $errors[] = 'النص يحتوي على ألفاظ غير لائقة';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
