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

    'facturama' => [
        'url' => env('APP_ENV') === 'production'
                 ? env('FACTURAMA_PROD_ENDPOINT')
                 : env('FACTURAMA_DEV_ENDPOINT', 'https://apisandbox.facturama.mx'),
        'user' => env('FACTURAMA_USERAGENT'),
        'password' => env('FACTURAMA_PASSWORD'),
        'tax_ish' => (float) env('TAX_ISH', 0.05),
        'tax_iva' => (float) env('TAX_IVA', 0.16),
    ],

    'billing' => [
        'project' => env('BILLING_PROJECT', 'both'),
    ],

];
