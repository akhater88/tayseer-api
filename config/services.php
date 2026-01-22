<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Infobip SMS Service
    |--------------------------------------------------------------------------
    */

    'infobip' => [
        'base_url' => env('INFOBIP_BASE_URL', 'https://api.infobip.com'),
        'api_key' => env('INFOBIP_API_KEY'),
        'sender_id' => env('INFOBIP_SENDER_ID', 'Tayseer'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase
    |--------------------------------------------------------------------------
    */

    'firebase' => [
        'credentials' => env('FIREBASE_CREDENTIALS', 'firebase-credentials.json'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],

];
