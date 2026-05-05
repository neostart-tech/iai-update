<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = "https://api.semoa-payments.ovh/sandbox-v3/auth";
$clientId = "cashpayescen";
$clientSecret = "cL3CjJReRuADdL8SAqQ1jQFHUjD2BMNV";
$username = "demoescen";
$password = 'YzyFX52s?T#0dqSr';

echo "--- Test SEMOA Auth ---\n";
echo "URL: $url\n";

$combinations = [
    "JSON direct" => [
        "username" => $username,
        "password" => $password,
        "client_id" => $clientId,
        "client_secret" => $clientSecret
    ],
    "Form params" => [
        "grant_type" => "password",
        "username" => $username,
        "password" => $password,
        "client_id" => $clientId,
        "client_secret" => $clientSecret
    ]
];

foreach ($combinations as $name => $data) {
    echo "\nEssai: $name\n";
    $response = Http::timeout(10);
    if ($name === "Form params") {
        $response = $response->asForm();
    }
    
    $res = $response->post($url, $data);
    
    echo "Status: " . $res->status() . "\n";
    echo "Body: " . $res->body() . "\n";
}

// Essai avec DEV-V3
$urlDev = "https://api.semoa-payments.ovh/dev-v3/auth";
echo "\nEssai avec DEV-V3 URL: $urlDev\n";
$resDev = Http::post($urlDev, $combinations["JSON direct"]);
echo "Status: " . $resDev->status() . "\n";
echo "Body: " . $resDev->body() . "\n";
