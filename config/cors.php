<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
        'user',
        'user/*'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',  // React/Vue frontend
        'http://127.0.0.1:3000',
        'http://localhost:8000',  // Laravel dev server
        env('APP_URL', 'http://localhost'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        '*',
        'Authorization',
        'X-Requested-With',
        'Content-Type',
        'X-Token-Auth',
        'X-CSRF-TOKEN',
    ],

    'exposed_headers' => [
        'Authorization',
        'X-CSRF-TOKEN'
    ],

    'max_age' => 0,

    'supports_credentials' => true,  // Required for Sanctum cookies

];