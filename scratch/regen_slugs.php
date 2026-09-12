<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$uvs = \App\Models\UniteValeur::all();
foreach ($uvs as $uv) {
    if (!$uv->nom) continue; // Skip if no name
    
    $slug = \Illuminate\Support\Str::slug($uv->nom);
    
    // Check for collisions
    $count = \App\Models\UniteValeur::where('slug', $slug)->where('id', '!=', $uv->id)->count();
    if ($count > 0) {
        $originalSlug = $slug;
        $i = 1;
        while (\App\Models\UniteValeur::where('slug', $slug)->where('id', '!=', $uv->id)->count() > 0) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }
    }
    $uv->slug = $slug;
    $uv->save();
}

echo "Slugs regenerated successfully without IDs!\n";
