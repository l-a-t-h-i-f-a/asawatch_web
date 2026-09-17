<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | AsaWatch has no web client — only the mobile app talks to /api/v1,
    | and native HTTP clients aren't subject to CORS anyway. This is left
    | explicitly empty so no browser origin is ever allowed in, rather than
    | relying on the absence of this file to imply the same thing.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    /*
     * Origin yang boleh memanggil API dari dalam browser — dipakai oleh webapp
     * (asawatch_webapp). Aplikasi Android tidak terpengaruh: CORS hanya berlaku
     * untuk permintaan dari halaman web.
     *
     * Bisa ditimpa di server tanpa mengubah kode, lewat .env:
     *   CORS_ALLOWED_ORIGINS=https://satu.com,https://dua.com
     */
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'CORS_ALLOWED_ORIGINS',
        implode(',', [
            'https://asawatch.enumatechnology.com',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
        ])
    ))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
