<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FraisScolarite;
use App\Models\TranchePaiement;

$frais = FraisScolarite::with(['niveau', 'filiere', 'anneeScolaire'])->get();

echo str_pad("ID", 5) . " | " . str_pad("ANNEE", 12) . " | " . str_pad("NIVEAU", 15) . " | " . str_pad("FILIERE", 25) . " | " . "MONTANT TOTAL\n";
echo str_repeat("-", 80) . "\n";

foreach ($frais as $f) {
    $montant = \DB::table('tranche_paiements')->where('frais_scolarite_id', $f->id)->sum('montant');
    echo str_pad($f->id, 5) . " | " . 
         str_pad($f->anneeScolaire->libelle ?? '?', 12) . " | " . 
         str_pad($f->niveau->libelle ?? '?', 15) . " | " . 
         str_pad($f->filiere->libelle ?? '--', 25) . " | " . 
         number_format($montant, 0, ',', ' ') . " FCFA\n";
}
