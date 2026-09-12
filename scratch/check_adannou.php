<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FraisEtudiant;
use App\Models\Echeance;

$fraisId = 643;
$frais = FraisEtudiant::with('echeances')->find($fraisId);

if ($frais) {
    echo "FraisEtudiant ID: " . $frais->id . " (Student ID: " . $frais->etudiant_id . ")\n";
    echo "Total Echeances: " . $frais->echeances->count() . "\n";
    foreach($frais->echeances->sortBy('ordre') as $e) {
        echo "- ID: {$e->id} | {$e->libelle} | Order: {$e->ordre} | Date: {$e->date_limite->format('Y-m-d')} | Status: {$e->statut} | Paid: {$e->montant_paye}/{$e->montant}\n";
    }
} else {
    echo "FraisEtudiant 643 not found.\n";
}
