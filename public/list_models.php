<?php

$apiKey = 'AIzaSyDp5q5JGhsKf634AW-Kna8u53hFttLbdAs';
$url = "https://generativelanguage.googleapis.com/v1/models?key=" . $apiKey;

echo "<h1>Listing des modèles Gemini</h1>";
echo "<p>Interrogation de l'API...</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "<p style='color:red'>ERREUR (Code $httpCode) : " . htmlspecialchars($response) . "</p>";
    // Essayer v1beta
    echo "<p>Essai avec v1beta...</p>";
    $urlBeta = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlBeta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "<p style='color:red'>ERREUR Beta (Code $httpCode) : " . htmlspecialchars($response) . "</p>";
    } else {
        afficherModeles(json_decode($response, true));
    }
} else {
    afficherModeles(json_decode($response, true));
}

function afficherModeles($data) {
    if (!isset($data['models'])) {
        echo "<pre>" . print_r($data, true) . "</pre>";
        return;
    }
    echo "<ul>";
    foreach ($data['models'] as $model) {
        $canGenerate = in_array('generateContent', $model['supportedGenerationMethods']) ? '✅' : '❌';
        echo "<li><strong>{$model['name']}</strong> (GenerateContent: $canGenerate) - " . htmlspecialchars($model['description'] ?? '') . "</li>";
    }
    echo "</ul>";
}
