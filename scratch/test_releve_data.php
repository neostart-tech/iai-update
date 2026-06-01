<?php
// Script pour tester l'API de récupération d'un relevé
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Etudiant;
use App\Services\NoteCalculationService;

$slug = 'b19295bc-9669-4775-b455-f233823ed95e'; // Le slug du log
$etudiant = Etudiant::where('slug', $slug)->first();

if (!$etudiant) {
    die("Etudiant non trouvé\n");
}

$service = app(NoteCalculationService::class);
$releves = $service->getRelevesByYear($etudiant);

echo json_encode($releves, JSON_PRETTY_PRINT);
