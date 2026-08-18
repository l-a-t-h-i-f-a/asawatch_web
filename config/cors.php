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

    'allowed_origins' => [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
