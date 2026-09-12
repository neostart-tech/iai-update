<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = App\Models\Role::with('permissions')->whereIn('slug', ['enseignant', 'etudiant', 'professeur'])->get();
foreach($roles as $r) {
    echo 'Role: ' . $r->slug . " (ID: " . $r->id . ")\n";
    foreach($r->permissions as $p) {
        echo ' - ' . $p->slug . "\n";
    }
}
