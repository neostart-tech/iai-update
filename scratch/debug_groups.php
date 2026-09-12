<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Group;
use App\Models\Niveau;
use App\Models\Filiere;

$groups = Group::with(['niveau', 'filieres'])->get();
$data = $groups->map(fn($g) => [
    'id' => $g->id,
    'nom' => $g->nom,
    'niveau_id' => $g->niveau_id,
    'niveau' => $g->niveau?->libelle,
    'filieres' => $g->filieres->map(fn($f) => ['id' => $f->id, 'nom' => $f->nom])
]);

echo json_encode($data, JSON_PRETTY_PRINT);
