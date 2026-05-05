<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AnneeScolaire;
use App\Models\CurrentEnv;

$active = AnneeScolaire::where('active', true)->first();
echo "Année scolaire active: " . ($active ? $active->nom . " (ID: " . $active->id . ")" : "AUCUNE") . "\n";

$all = AnneeScolaire::all();
foreach($all as $a) {
    echo "- " . $a->nom . " (ID: " . $a->id . ", Active: " . ($a->active ? 'OUI' : 'NON') . ")\n";
}
