<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $paiement = \App\Models\Paiement::updateOrCreate(
        ['reference' => 'REF_TEST_ANTIGRAVITY'],
        [
            'etudiant_id' => 1, // Assurez-vous qu'un étudiant ID 1 existe ou remplacez
            'montant' => 100,
            'status' => 'en_attente',
            'mode_paiement' => 'semoa',
            'date_paiement' => now(),
            'slug' => 'pay-test-antigravity'
        ]
    );
    echo "Paiement de test créé/mis à jour avec succès.\n";
} catch (\Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
