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

    /*
     * Masuk lewat akun Google. Nilainya adalah **Web client ID** dari Google
     * Cloud Console -- yang sama persis dengan `idKlienGoogle` di aplikasi
     * Flutter. Ia dipakai untuk satu hal: mencocokkan klaim `aud` pada ID
     * token. Tanpa pencocokan itu, ID token yang diambil dari aplikasi Android
     * lain bisa dipakai masuk sebagai orang lain di sini.
     *
     * Bukan rahasia -- nilai yang sama ikut ke dalam setiap APK -- tetapi tetap
     * lewat env supaya project Google bisa berbeda antara pengembangan dan
     * produksi tanpa menyunting kode.
     */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
