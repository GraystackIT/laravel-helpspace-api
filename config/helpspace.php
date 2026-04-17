<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HelpSpace API Key
    |--------------------------------------------------------------------------
    | Your HelpSpace Bearer token (API key).
    | Generate one in your HelpSpace account settings.
    */
    'api_key' => env('HELPSPACE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | HelpSpace Client ID
    |--------------------------------------------------------------------------
    | Your HelpSpace Hs-Client-Id header value.
    */
    'client_id' => env('HELPSPACE_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | The base URL for the HelpSpace API.
    | Override when using a proxy or staging environment.
    */
    'base_url' => env('HELPSPACE_BASE_URL', 'https://api.helpspace.com'),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'per_page' => 20,
    ],
];
