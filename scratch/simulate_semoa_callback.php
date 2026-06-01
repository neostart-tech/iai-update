<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "--- DEBUT DU TEST DE SIMULATION SEMOA ---\n";

// 1. Création du paiement en attente
$reference = "REF_TEST_" . time();
try {
    $paiement = \App\Models\Paiement::create([
        'etudiant_id' => 1,
        'montant' => 500,
        'reference' => $reference,
        'status' => 'en_attente',
        'mode_paiement' => 'semoa',
        'date_paiement' => now()
    ]);
    echo "1. Paiement créé en base avec référence : $reference\n";
} catch (\Exception $e) {
    echo "Erreur création paiement : " . $e->getMessage() . "\n";
    exit;
}

// 2. Génération du JWT simulé
// Format : header.payload.signature
$header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
$payload = base64_encode(json_encode([
    'order_reference' => $reference,
    'state' => 'SUCCESS',
    'amount' => 500,
    'event' => 'PAYMENT_COMPLETED'
]));
$signature = "fake_signature";
$jwt = "$header.$payload.$signature";

echo "2. Simulation de l'appel Webhook (POST) vers le callback...\n";

// 3. Appel du contrôleur via une requête interne (simulée)
$request = \Illuminate\Http\Request::create('/api/semoa-callback-url', 'POST', [], [], [], [], $jwt);
$controller = new \App\Http\Controllers\Api\SemoaCallBackController();

try {
    $response = $controller($request);
    echo "3. Réponse du contrôleur : " . $response->getContent() . "\n";
    
    // 4. Vérification en base de données
    $paiementFrais = \App\Models\Paiement::where('reference', $reference)->first();
    echo "4. Statut final du paiement en base : " . $paiementFrais->status . "\n";
    
    if ($paiementFrais->status === 'valide') {
        echo "\n>>> SUCCÈS : Le paiement a été automatiquement validé ! <<<\n";
    } else {
        echo "\n>>> ÉCHEC : Le paiement est toujours en attente. <<<\n";
    }
} catch (\Exception $e) {
    echo "Erreur lors de l'appel du contrôleur : " . $e->getMessage() . "\n";
}

echo "--- FIN DU TEST ---\n";
