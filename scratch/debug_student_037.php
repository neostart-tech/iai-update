<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\Echeance;
use App\Models\FraisEtudiant;

$matricule = '037-ESC-2025';
$etudiant = Etudiant::where('matricule', $matricule)->first();

if (!$etudiant) {
    die("Étudiant non trouvé: $matricule\n");
}

echo "--- Étudiant: {$etudiant->nom} {$etudiant->prenom} (ID: {$etudiant->id}) ---\n";

$frais = FraisEtudiant::where('etudiant_id', $etudiant->id)
    ->whereHas('anneeScolaire', function($q) { $q->where('active', true); })
    ->with('echeances')
    ->get();

foreach ($frais as $f) {
    echo "Frais ID: {$f->id}, Scolarité: {$f->fraisScolarite?->libelle}, Type: {$f->type_paiement}\n";
    foreach ($f->echeances as $e) {
        echo "  - Echeance ID: {$e->id}, Libelle: {$e->libelle}, Montant: {$e->montant}, Payé: {$e->montant_paye}, Statut: {$e->statut}\n";
    }
}

$paiements = Paiement::where('etudiant_id', $etudiant->id)->get();
echo "\n--- Paiements Trouvés: " . $paiements->count() . " ---\n";
foreach ($paiements as $p) {
    echo "Paiement ID: {$p->id}, Montant: {$p->montant}, Status: {$p->status}, Payable Type: {$p->payable_type}, Payable ID: {$p->payable_id}\n";
}
