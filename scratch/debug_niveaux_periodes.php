<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Niveau;
use App\Models\Periode;

echo "--- NIVEAUX ---\n";
foreach (Niveau::all() as $n) {
    echo "ID: {$n->id} | Libelle: {$n->libelle}\n";
}

echo "\n--- PERIODES ---\n";
foreach (Periode::all() as $p) {
    echo "ID: {$p->id} | Nom: {$p->nom}\n";
}
