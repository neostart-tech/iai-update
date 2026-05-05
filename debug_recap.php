<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Services\PaiementEtudiantService;
use App\Models\Etudiant;

$service = new PaiementEtudiantService();
$etudiant = Etudiant::first();

if ($etudiant) {
    try {
        $recap = $service->getRecap($etudiant->id);
        echo "RECAP pour " . $etudiant->nom . ":\n";
        print_r($recap);
    } catch (Exception $e) {
        echo "ERREUR: " . $e->getMessage();
    }
} else {
    echo "Aucun étudiant trouvé.";
}
