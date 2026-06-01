<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Etudiant;

echo "Test de génération de matricule :\n";
$year = 2025;
echo "Année : $year\n";

// Simulation de plusieurs générations
for ($i = 0; $i < 5; $i++) {
    // Note: cette simulation ne reflète pas le count réel en DB si on n'insère pas
    // Mais on peut au moins vérifier le format
    $matricule = Etudiant::generateNextMatricule($year);
    echo "Généré ($i) : $matricule\n";
}
