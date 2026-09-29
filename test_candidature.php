<?php

// script.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidature;
use Illuminate\Http\Request;
use App\Http\Controllers\CandidatureController;
use App\Models\AnneeScolaire;
use App\Models\ConcoursSession;
use Illuminate\Support\Str;

echo "--- Démarrage du test manuel ---\n";

$email = 'test_manuel_' . time() . '@example.com';
echo "Email utilisé : $email\n";

$annee = AnneeScolaire::first();
if (!$annee) {
    $annee = AnneeScolaire::create(['nom' => '2023-2024', 'active' => true, 'code' => '2324']);
}

$session = ConcoursSession::where('annee_scolaire_id', $annee->id)->first();
if (!$session) {
    $session = ConcoursSession::create(['annee_scolaire_id' => $annee->id, 'nom' => 'Session 1', 'date_debut' => now(), 'date_fin' => now()->addDays(10)]);
}

// 1. Création d'une candidature incomplète (brouillon)
$draftToken = Str::random(48);
$incomplete = Candidature::create([
    'email' => $email,
    'nom' => 'AncienNom',
    'prenom' => 'AncienPrenom',
    'genre' => 'M',
    'date_naissance' => '2000-01-01',
    'lieu_naissance' => 'Paris',
    'nationalite' => 'Togo',
    'tel' => '0000',
    'soumis_le' => null, // INCOMPLET
    'draft_token' => $draftToken,
    'annee_scolaire_id' => $annee->id,
    'concours_session_id' => $session->id,
    'password' => bcrypt('password'),
    'code' => rand(100000, 999999)
]);

echo "Candidature incomplète créée. ID: {$incomplete->id}, Token: {$draftToken}\n";

// 2. Simulation de l'appel API pour l'étape 1 avec le MÊME EMAIL
$request = Request::create('/api/candidatures/etape1', 'POST', [
    'nom' => 'NouveauNom',
    'prenom' => 'NouveauPrenom',
    'genre' => 'M',
    'date_naissance' => '2000-01-01',
    'lieu_naissance' => 'Lomé',
    'nationalite' => 'Togo',
    'tel' => '12345678',
    'email' => $email, // LE MEME EMAIL
]);

$controller = new CandidatureController();
$response = $controller->soumettreEtape1($request);

echo "Statut de la réponse HTTP : " . $response->getStatusCode() . "\n";
echo "Contenu : " . $response->getContent() . "\n";

$candidatureApres = Candidature::find($incomplete->id);
echo "Nom après l'appel : " . $candidatureApres->nom . " (attendu: NouveauNom)\n";
echo "Draft token modifié ? " . ($candidatureApres->draft_token === $draftToken ? 'Non (c\'est parfait)' : 'Oui (erreur)') . "\n";

// 3. Simulation si la candidature est déjà soumise
$candidatureApres->update(['soumis_le' => now()]);

$request2 = Request::create('/api/candidatures/etape1', 'POST', [
    'nom' => 'EncoreNouveau',
    'prenom' => 'EncoreNouveau',
    'genre' => 'M',
    'date_naissance' => '2000-01-01',
    'lieu_naissance' => 'Lomé',
    'nationalite' => 'Togo',
    'tel' => '12345678',
    'email' => $email, // LE MEME EMAIL
]);

$response2 = $controller->soumettreEtape1($request2);
echo "\nStatut de la 2ème réponse HTTP (dossier soumis) : " . $response2->getStatusCode() . "\n";
echo "Contenu : " . $response2->getContent() . "\n";

// Nettoyage
$candidatureApres->delete();
echo "Test terminé, données nettoyées.\n";
