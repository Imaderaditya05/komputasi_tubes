<?php

$midtransServerKey = trim((string) env('MIDTRANS_SERVER_KEY', ''));
$midtransClientKey = trim((string) env('MIDTRANS_CLIENT_KEY', ''));

// Hanya prefiks SB-Mid-server-* yang memaksa sandbox. Selain itu ikuti MIDTRANS_IS_PRODUCTION di .env
// (Midtrans: kunci sandbox resmi berawalan SB-Mid-*; Mid-* di dashboard biasanya production.)
$midtransIsProduction = str_starts_with($midtransServerKey, 'SB-Mid-server-')
    ? false
    : filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);

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

    /*
    | Google Maps JavaScript API (lacak pesanan — rute & peta).
    | Aktifkan: Maps JavaScript API, Directions API, Geocoding API di Google Cloud.
    | Batasi kunci dengan referrer HTTP (situs Anda).
    */
    'google_maps' => [
        'key' => trim((string) env('GOOGLE_MAPS_API_KEY', '')),
    ],

    'midtrans' => [
        'merchant_id' => trim((string) env('MIDTRANS_MERCHANT_ID', '')),
        'client_key' => $midtransClientKey,
        'server_key' => $midtransServerKey,
        'is_production' => $midtransIsProduction,
        // Bundle CA (resources/certs/cacert.pem) agar cURL tidak memakai curl.cainfo php.ini yang rusak (mis. Laragon Windows)
        'cacert_path' => env('MIDTRANS_CACERT_PATH', resource_path('certs/cacert.pem')),
    ],

    /*
    | Chat kurir (halaman lacak) — balasan via OpenAI jika OPENAI_API_KEY diset.
    */
    'openai' => [
        'api_key' => trim((string) env('OPENAI_API_KEY', '')),
        'model' => trim((string) env('OPENAI_MODEL', 'gpt-4o-mini')),
    ],

    /*
    | Nomor telepon restoran / hotline untuk tombol "Hubungi restoran" (format internasional, mis. +6281234567890)
    */
    'surprisebite' => [
        'restaurant_hotline' => trim((string) env('RESTAURANT_HOTLINE', '')),
    ],

];
