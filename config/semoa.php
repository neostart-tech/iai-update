<?php

return [
    'url' => env('SEMOA_URL', 'https://api.semoa-payments.ovh/sandbox-v3'),
    'client_id' => env('SEMOA_CLIENT_ID'),
    'client_secret' => env('SEMOA_CLIENT_SECRET'),
    'username' => env('SEMOA_USERNAME'),
    'password' => env('SEMOA_PASSWORD'),
    'api_key' => env('SEMOA_API_KEY'),
    'api_reference' => env('SEMOA_API_REFERENCE', '20'),
    'in_sandbox' => env('SEMOA_IN_SANDBOX', true),
];