<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\Syllabus::first(); 
if ($s) { 
    print_r($s->toArray()); 
} else {
    echo "No syllabus found.\n";
}
