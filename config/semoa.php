<?php

return [
    'url' => env('CASHPAY_URL', env('SEMOA_URL', 'https://api.semoa-payments.ovh/sandbox-v3')),
    'client_id' => env('CASHPAY_CLIENT_ID', env('SEMOA_CLIENT_ID')),
    'client_secret' => env('CASHPAY_CLIENT_SECRET', env('SEMOA_CLIENT_SECRET')),
    'username' => env('CASHPAY_USERNAME', env('SEMOA_USERNAME')),
    'password' => env('CASHPAY_PASSWORD', env('SEMOA_PASSWORD')),
    'api_key' => env('CASHPAY_API_KEY', env('SEMOA_API_KEY')),
    'api_reference' => env('CASHPAY_API_REFERENCE', env('SEMOA_API_REFERENCE', '20')),
    'gateway_reference' => env('SEMOA_GATEWAY_REFERENCE'),
    'in_sandbox' => env('SEMOA_IN_SANDBOX', true),
];