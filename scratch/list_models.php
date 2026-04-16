<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Charge l'environnement Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = 'AIzaSyDp5q5JGhsKf634AW-Kna8u53hFttLbdAs';
$url = "https://generativelanguage.googleapis.com/v1/models?key=" . $apiKey;

echo "Interrogation de l'API pour lister les modèles...\n";
$response = \Illuminate\Support\Facades\Http::withoutVerifying()->get($url);

if ($response->failed()) {
    echo "ERREUR : " . $response->body() . "\n";
} else {
    $models = $response->json();
    echo "Modèles disponibles :\n";
    foreach ($models['models'] as $model) {
        echo "- " . $model['name'] . " (Supporte generateContent: " . (in_array('generateContent', $model['supportedGenerationMethods']) ? 'OUI' : 'NON') . ")\n";
    }
}
