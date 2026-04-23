<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logo = \App\Models\Configuration::where('key', 'logo_etablissement')->first();
echo "Logo: " . ($logo ? $logo->value : 'NULL') . "\n";
