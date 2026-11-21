<?php

return [

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

    'payments' => [
        'default' => env('PAYMENT_GATEWAY', 'fake'),
    ],

    'tbank' => [
        'api_url' => env('TBANK_API_URL', 'https://securepay.tinkoff.ru'),
        'ca_bundle' => env('TBANK_CA_BUNDLE'),
        'terminal_key' => env('TBANK_TERMINAL_KEY'),
        'password' => env('TBANK_PASSWORD'),
        'notification_url' => env('TBANK_NOTIFICATION_URL'),
        'taxation' => env('TBANK_TAXATION', 'usn_income'),
        'tax' => env('TBANK_TAX', 'none'),
        'payment_method' => env('TBANK_PAYMENT_METHOD', 'full_payment'),
        'payment_object' => env('TBANK_PAYMENT_OBJECT', 'service'),
        'timeout' => env('TBANK_TIMEOUT', 10),
    ],

];
