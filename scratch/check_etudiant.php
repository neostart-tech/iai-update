<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Etudiant;

$etudiant = Etudiant::first();
if ($etudiant) {
    echo "ID: " . $etudiant->id . "\n";
    echo "Nom: " . $etudiant->nom . "\n";
    echo "Matricule: " . $etudiant->matricule . "\n";
} else {
    echo "Aucun étudiant trouvé dans la base de données.\n";
}
