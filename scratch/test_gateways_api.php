<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gateways = \App\Models\SemoaGateway::where('is_active', true)->get();
echo "Nombre de passerelles en base : " . count($gateways) . "\n";
foreach ($gateways as $g) {
    echo "- {$g->libelle} ({$g->reference}) | PSP: {$g->psp_libelle} | Mode: {$g->methode}\n";
}
