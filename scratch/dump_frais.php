<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FraisScolarite;

$data = "";
$frais = FraisScolarite::with(['niveau', 'filiere', 'anneeScolaire', 'tranchepaiement'])->get();

foreach ($frais as $f) {
    $sum = $f->tranchepaiement->sum('montant');
    $data .= "ID: {$f->id} | ANNEE: " . ($f->anneeScolaire->libelle ?? '?') . " | NIVEAU: " . ($f->niveau->libelle ?? '?') . " | FILIERE: " . ($f->filiere->libelle ?? '--') . " | GENRE: {$f->genre->value} | MONTANT: " . number_format($sum, 0, '', '') . "\n";
}

file_put_contents('frais_dump.txt', $data);
echo "Dump terminé.\n";
