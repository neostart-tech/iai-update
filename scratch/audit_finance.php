<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Etudiant;

$anneeId = 5; // Supposons l'année courante est 5
echo "--- DIAGNOSTIC PAIEMENTS ADANNOU JOSUE ---\n";
$etudiant = Etudiant::where('nom', 'LIKE', '%ADANNOU%')->first();

if (!$etudiant) {
    echo "Étudiant non trouvé.\n";
    exit;
}

echo "ID Étudiant: " . $etudiant->id . " - " . $etudiant->nom_complet . "\n";

$paiements = Paiement::where('etudiant_id', $etudiant->id)->get();
echo "Nombre de paiements trouvés: " . $paiements->count() . "\n";

foreach ($paiements as $p) {
    echo "ID: {$p->id} | Montant: {$p->montant} | Status: {$p->status} | Nature: {$p->nature_paiement} | Date: {$p->date_paiement}\n";
    
    // Check school year link
    $groups = $etudiant->etudiantGroups()->where('annee_scolaire_id', $anneeId)->get();
    echo "  -> Lié à l'Année {$anneeId}: " . ($groups->count() > 0 ? 'OUI' : 'NON') . " | Statut: " . ($groups->first()->pivot->statut_scolaire ?? 'N/A') . "\n";
}

echo "\n--- TEST SCAN FINANCE SERVICE ---\n";
$queryBase = Paiement::where('status', 'valide')
    ->whereHas('etudiant.etudiantGroups', function($q) use ($anneeId) {
        $q->where('annee_scolaire_id', $anneeId);
    });
echo "Total Global Validé Année 5: " . $queryBase->sum('montant') . "\n";
