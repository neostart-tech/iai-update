<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FraisEtudiant;
use App\Models\Echeance;
use Carbon\Carbon;

$countFrais = FraisEtudiant::count();
$countEcheances = Echeance::count();

echo "Total FraisEtudiant: " . $countFrais . "\n";
echo "Total Echeances: " . $countEcheances . "\n";

$unpaidEcheances = Echeance::whereRaw('montant > montant_paye')->count();
echo "Total Unpaid Echeances: " . $unpaidEcheances . "\n";

$futureUnpaid = Echeance::where('date_limite', '>=', now())->whereRaw('montant > montant_paye')->count();
echo "Future Unpaid Echeances: " . $futureUnpaid . "\n";

$pastUnpaid = Echeance::where('date_limite', '<', now())->whereRaw('montant > montant_paye')->count();
echo "Past Unpaid Echeances: " . $pastUnpaid . "\n";

$firstFrais = FraisEtudiant::with('echeances')->where('est_en_abandon', false)->first();
if ($firstFrais) {
    echo "First FraisEtudiant ID: " . $firstFrais->id . " (Student: " . $firstFrais->etudiant_id . ")\n";
    echo "Echeances count for this student: " . $firstFrais->echeances->count() . "\n";
    foreach($firstFrais->echeances as $e) {
        echo "- " . $e->libelle . " | Date: " . $e->date_limite . " | Status: " . $e->statut . " | Paid: " . $e->montant_paye . "/" . $e->montant . "\n";
    }
} else {
    echo "No active FraisEtudiant found.\n";
}
