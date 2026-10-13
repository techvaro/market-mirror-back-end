<?php

return [

    'agora' => [
        'app_id' => env('AGORA_APP_ID'),
        'app_certificate' => env('AGORA_APP_CERTIFICATE'),
    ],

    'flutterwave' => [
        // All Flutterwave credentials must live in .env and are only ever read
        // through these config keys — never call env() outside of config files.
        'base_url' => env('FLW_BASE_URL', 'https://api.flutterwave.com/v3'),
        'public_key' => env('FLW_PUBLIC_KEY'),
        'secret_key' => env('FLW_SECRET_KEY'),
        // Secret hash configured on the Flutterwave dashboard; verifies webhooks.
        'secret_hash' => env('FLW_SECRET_HASH'),
        'currency' => env('FLW_CURRENCY', 'NGN'),
        'redirect_url' => env('FLW_REDIRECT_URL', env('FRONTEND_BUYER_URL', 'http://localhost:5173').'/payment/callback'),
        'transfer_bank_code' => env('FLW_DEFAULT_BANK_CODE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'buyer_frontend' => env('FRONTEND_BUYER_URL', 'http://localhost:5173'),
        'vendor_frontend' => env('FRONTEND_VENDOR_URL', 'http://localhost:5175'),
    ],
];
