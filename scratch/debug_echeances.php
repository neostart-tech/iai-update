<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AnneeScolaire;
use App\Models\Echeance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$active = AnneeScolaire::where('active', true)->first();
$anneeId = $active ? $active->id : null;

echo "Active Year: " . ($active ? $active->libelle : "None") . " (ID: $anneeId)\n";
echo "Current Date: " . Carbon::now()->toDateTimeString() . "\n\n";

if ($anneeId) {
    $stats = DB::table('echeances')
        ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
        ->where('frais_etudiants.annee_scolaire_id', $anneeId)
        ->select(
            DB::raw('MIN(date_limite) as min_date'),
            DB::raw('MAX(date_limite) as max_date'),
            DB::raw('COUNT(*) as total_count')
        )
        ->first();

    echo "Stats for the active year:\n";
    echo "Total installments: " . $stats->total_count . "\n";
    echo "Earliest: " . ($stats->min_date ?? 'N/A') . "\n";
    echo "Latest: " . ($stats->max_date ?? 'N/A') . "\n";
    
    $future = DB::table('echeances')
        ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
        ->where('frais_etudiants.annee_scolaire_id', $anneeId)
        ->where('echeances.date_limite', '>=', Carbon::now())
        ->count();
    
    echo "Future installments: " . $future . "\n";
    
    if ($future == 0 && $stats->total_count > 0) {
        echo "\nREASON: All installments for this academic year are in the past.\n";
    }
} else {
    echo "No active academic year found.\n";
}
