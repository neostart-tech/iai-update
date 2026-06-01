<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$releves = \App\Models\ReleveNote::whereNull('slug')->orWhere('slug', '')->get();
echo "Found " . $releves->count() . " releves without slug.\n";

foreach ($releves as $releve) {
    $releve->slug = uniqid();
    $releve->save();
    echo "Updated Releve ID: " . $releve->id . " with slug: " . $releve->slug . "\n";
}

echo "Done.\n";
