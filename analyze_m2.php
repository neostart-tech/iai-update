<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FraisScolarite;

$m2_frais = FraisScolarite::withoutGlobalScopes()
    ->where('niveau_id', function($q) {
        $q->select('id')->from('niveaux')->where('libelle', 'LIKE', '%Master 2%')->limit(1);
    })
    ->with(['anneeScolaire', 'tranchepaiement'])
    ->get();

echo "ANALYSE DES TARIFS MASTER 2 :\n";
foreach ($m2_frais as $f) {
    $sum = $f->tranchepaiement->sum('montant');
    echo "ID: {$f->id} | ANNEE: " . ($f->anneeScolaire->nom ?? '?') . " | FILIERE: " . ($f->filiere_id ?? 'NULL') . " | MONTANT: " . number_format($sum, 0, '', ' ') . " FCFA\n";
}
