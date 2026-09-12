<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "UniteValeur columns:\n";
print_r(Schema::getColumnListing('unite_valeurs'));
echo "Syllabus columns:\n";
print_r(Schema::getColumnListing('syllabuses'));
