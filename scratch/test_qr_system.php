<?php

use App\Services\QRCodeService;
use App\Models\Etudiant;

/**
 * Script de test pour vérifier la génération de QR codes
 * À exécuter via: php artisan tinker
 */

// Exemple de données de test pour un relevé
$testReleveData = [
    'anne' => '2023-2024',
    'periode_nom' => 'Semestre 1',
    'moyenne_generale' => '14.75',
    'total_credits_valides' => 30,
    'total_credits_non_valides' => 0,
    'releve_grouped' => [
        'Informatique Générale' => [
            [
                'uv' => 'Programmation Web',
                'devoir' => '16.00',
                'interrogation' => '14.00',
                'examen' => '15.00',
                'moyenne_uv' => '15.00',
                'validation' => 'Validé',
                'coefficient' => 3
            ]
        ]
    ]
];

// Test de création d'un étudiant fictif
$testEtudiant = new Etudiant();
$testEtudiant->id = 1;
$testEtudiant->nom = 'TESTE';
$testEtudiant->prenom = 'QRCode';
$testEtudiant->genre = 'M';

// Test de génération du QR Code
$qrService = new QRCodeService();

try {
    echo "=== Test de génération QR Code ===\n";
    echo "Étudiant: {$testEtudiant->nom} {$testEtudiant->prenom}\n";
    echo "Filière: Informatique\n";
    echo "Moyenne: {$testReleveData['moyenne_generale']}\n";
    
    // Note: Ce test ne fonctionnera que si l'étudiant existe vraiment en BDD
    // $qrCode = $qrService->generateReleveQRCode($testEtudiant, $testReleveData, 'Informatique');
    // echo "QR Code généré avec succès !\n";
    
    echo "\nPour tester complètement:\n";
    echo "1. Aller sur /administration/releves/generer/{id_etudiant}\n";
    echo "2. Vérifier que le PDF contient le QR Code\n";
    echo "3. Scanner le QR ou copier le hash pour vérification\n";
    echo "4. Aller sur /verifier-releve pour tester la vérification\n";
    
} catch (Exception $e) {
    echo "Erreur lors du test: " . $e->getMessage() . "\n";
}

echo "\n=== Routes disponibles ===\n";
echo "- Génération: /administration/releves/generer/{id}\n";
echo "- Vérification publique: /verifier-releve\n";
echo "- API vérification: /verifier-releve/api/verification (POST)\n";
echo "- Vérification directe: /verifier-releve/verification/{hash}\n";