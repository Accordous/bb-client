<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Banco do Brasil API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Banco do Brasil API.
    |
    */

    // API Base URL
    'base_url' => env('BB_API_BASE_URL', 'https://api.hm.bb.com.br'),
    
    // OAuth URL
    'oauth_url' => env('BB_OAUTH_URL', 'https://oauth.hm.bb.com.br'),
    
    // OAuth Credentials
    'client_id' => env('BB_CLIENT_ID', ''),
    'client_secret' => env('BB_CLIENT_SECRET', ''),
    
    // Developer Application Key
    'developer_application_key' => env('BB_DEVELOPER_APPLICATION_KEY', ''),
    
    // Cobrança Configuration
    'cobranca' => [
        'gw_app_key' => env('BB_GW_APP_KEY', ''),
    ],
    
    // Timeout settings (in seconds)
    'timeout' => env('BB_API_TIMEOUT', 30),
    'connect_timeout' => env('BB_API_CONNECT_TIMEOUT', 10),
]; 