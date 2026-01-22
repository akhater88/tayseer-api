<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'length' => env('OTP_LENGTH', 6),
        'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 5),
        'max_attempts' => env('OTP_MAX_ATTEMPTS', 3),
        'cooldown_minutes' => env('OTP_COOLDOWN_MINUTES', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | App Limits
    |--------------------------------------------------------------------------
    */

    'daily_interest_limit' => env('DAILY_INTEREST_LIMIT', 20),
    'max_favorites' => env('MAX_FAVORITES', 50),
    'max_photos' => env('MAX_PHOTOS', 5),
    'profile_completion_required' => env('PROFILE_COMPLETION_REQUIRED', 70),

    /*
    |--------------------------------------------------------------------------
    | Guardian Settings
    |--------------------------------------------------------------------------
    */

    'guardian' => [
        'invitation_expiry_days' => 30,
        'cooldown_after_rejection_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Online Status
    |--------------------------------------------------------------------------
    */

    'online_threshold_minutes' => 15,

];
