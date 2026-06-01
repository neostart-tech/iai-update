<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AnneeScolaire;
use App\Models\AnneeFiliere;
use App\Models\FraisEtudiant;
use App\Models\Echeance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$annee = AnneeScolaire::where('active', true)->first();
$anneeId = $annee ? $annee->id : 1;

echo "--- DEBUG PRÉVISIONS DASHBOARD ---\n";
echo "Date actuelle (Carbon::now()): " . Carbon::now()->toDateTimeString() . "\n";
echo "Année Scolaire Active: ID {$anneeId} (" . ($annee ? $annee->libelle : 'N/A') . ")\n";

// 1. Comptage des échéances totales
$totalEcheances = DB::table('echeances')
    ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
    ->where('frais_etudiants.annee_scolaire_id', $anneeId)
    ->count();
echo "Total échéances pour cette année: {$totalEcheances}\n";

// 2. Échéances passées vs futures
$passees = DB::table('echeances')
    ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
    ->where('frais_etudiants.annee_scolaire_id', $anneeId)
    ->where('echeances.date_limite', '<', Carbon::now())
    ->count();

$futures = DB::table('echeances')
    ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
    ->where('frais_etudiants.annee_scolaire_id', $anneeId)
    ->where('echeances.date_limite', '>=', Carbon::now())
    ->count();

echo "Échéances PASSÉES: {$passees}\n";
echo "Échéances FUTURES: {$futures}\n";

// 3. Détail des mois pour les prévisions
$previsions = DB::table('echeances')
    ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
    ->select(DB::raw('MONTH(echeances.date_limite) as mois'), DB::raw('YEAR(echeances.date_limite) as annee'), DB::raw('SUM(echeances.montant) as total'), DB::raw('COUNT(*) as count'))
    ->where('frais_etudiants.annee_scolaire_id', $anneeId)
    ->where('frais_etudiants.est_en_abandon', false)
    ->where('echeances.date_limite', '>=', Carbon::now())
    ->groupBy('annee', 'mois')
    ->orderBy('annee')
    ->orderBy('mois')
    ->get();

echo "\n--- PRÉVISIONS DÉTAILLÉES ---\n";
if ($previsions->isEmpty()) {
    echo "Aucune prévision trouvée (toutes les échéances sont peut-être passées).\n";
    
    // Check if there are ANY deadlines upcoming by year
    $lastDeadline = DB::table('echeances')
        ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
        ->where('frais_etudiants.annee_scolaire_id', $anneeId)
        ->max('date_limite');
    echo "Dernière échéance enregistrée pour cette année: " . ($lastDeadline ?: 'AUCUNE') . "\n";
} else {
    foreach ($previsions as $p) {
        echo "Mois: {$p->mois}/{$p->annee} | Montant: {$p->total} | Count: {$p->count}\n";
    }
}

// 4. Etudiants en retard
echo "\n--- ETUDIANTS EN RETARD ---\n";
$etudiantsEnRetard = DB::table('echeances')
    ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
    ->where('frais_etudiants.annee_scolaire_id', $anneeId)
    ->where('echeances.date_limite', '<', Carbon::now())
    ->whereRaw('echeances.montant_paye < echeances.montant')
    ->count();
echo "Nombre total d'échéances en retard (impayées): {$etudiantsEnRetard}\n";
