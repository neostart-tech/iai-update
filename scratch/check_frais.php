<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$etudiant = \App\Models\Etudiant::where('matricule', 'LIKE', '%ABOTCHI%')->orWhere('nom', 'LIKE', '%ABOTCHI%')->first();
if (!$etudiant) {
    echo "Etudiant non trouvé\n";
    exit;
}

$frais = \App\Models\FraisEtudiant::where('etudiant_id', $etudiant->id)->get();
echo "FraisEtudiant for " . $etudiant->nom . " (ID: " . $etudiant->id . "):\n";
foreach ($frais as $f) {
    echo "ID: {$f->id} | FraisScolariteID: {$f->frais_scolarite_id} | Montant Initial: {$f->montant_initial} | Montant Apres Bourse: {$f->montant_apres_bourse}\n";
}

echo "\n--- Echeances ---\n";
foreach ($frais as $f) {
    foreach ($f->echeances as $e) {
        echo "ID: {$e->id} | Libelle: {$e->libelle} | Montant: {$e->montant}\n";
    }
}
