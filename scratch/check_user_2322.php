<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$etudiantId = 2322;

$etudiant = \App\Models\Etudiant::find($etudiantId);
$user = \DB::table('users')->where('id', $etudiantId)->first();

echo "Etudiant ID $etudiantId: " . ($etudiant ? "TROUVÉ ($etudiant->nom $etudiant->prenom)" : "NON TROUVÉ") . "\n";
echo "User ID $etudiantId in users table: " . ($user ? "TROUVÉ" : "NON TROUVÉ") . "\n";
