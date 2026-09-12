<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UniteValeur;

$uv = UniteValeur::with('matiere')->first();
echo "UV toArray:\n";
print_r($uv->toArray());
