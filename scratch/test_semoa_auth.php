<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\SemoaService;

echo "Tentative d'authentification SEMOA...\n";
$service = new SemoaService();

try {
    // On appelle une méthode privée via reflection ou on ruse
    // Ou simplement on essaie d'initier un petit truc
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getToken');
    $method->setAccessible(true);
    
    $token = $method->invoke($service);
    echo "Token obtenu avec succès ! \n";
    echo "Début du token : " . substr($token, 0, 20) . "...\n";
} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}
