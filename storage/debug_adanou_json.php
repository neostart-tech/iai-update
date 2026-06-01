<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FraisEtudiant;
use App\Models\Etudiant;

$matricule = '037-ESC-2025';
$etudiant = Etudiant::where('matricule', $matricule)->first();

if (!$etudiant) {
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$fraisEtudiant = FraisEtudiant::with(['echeances', 'paiements'])
    ->where('etudiant_id', $etudiant->id)
    ->whereHas('anneeScolaire', function($q) { $q->where('active', true); })
    ->first();

if (!$fraisEtudiant) {
    echo json_encode(['error' => 'FraisEtudiant not found']);
    exit;
}

$totalPayeGlobal = $fraisEtudiant->paiements()->where('status', 'valide')->sum('montant');
$allPaiements = $fraisEtudiant->paiements()->get()->toArray();

$results = [
    'student' => $etudiant->nom . ' ' . $etudiant->prenom,
    'total_global_valide' => $totalPayeGlobal,
    'all_payments' => $allPaiements,
    'echeances' => []
];

$resteGlobalADistribuer = $totalPayeGlobal;

foreach ($fraisEtudiant->echeances as $e) {
    $payeDirect = $e->paiements()->where('status', 'valide')->sum('montant');
    
    $creditGlobalAttribué = 0;
    if ($resteGlobalADistribuer > 0 && $payeDirect < $e->montant) {
        $besoin = $e->montant - $payeDirect;
        $creditGlobalAttribué = min($besoin, $resteGlobalADistribuer);
        $resteGlobalADistribuer -= $creditGlobalAttribué;
    }

    $results['echeances'][] = [
        'id' => $e->id,
        'libelle' => $e->libelle,
        'montant' => $e->montant,
        'paye_direct' => $payeDirect,
        'credit_global' => $creditGlobalAttribué,
        'total_calcule' => $payeDirect + $creditGlobalAttribué,
        'statut_final' => ($payeDirect + $creditGlobalAttribué >= $e->montant) ? 'paye' : (($payeDirect + $creditGlobalAttribué > 0) ? 'partiel' : 'en_attente')
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);
