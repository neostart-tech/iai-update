<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AnneeScolaire;

$annes = AnneeScolaire::all();
foreach ($annes as $a) {
    echo "ID: {$a->id} | NOM: {$a->nom} | LIBELLE: {$a->libelle} | ACTIVE: " . ($a->active ? "OUI" : "NON") . "\n";
}
