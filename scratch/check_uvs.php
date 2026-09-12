<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$uvs = \App\Models\UniteValeur::with('matiere')->get();
foreach ($uvs as $uv) {
    echo "UV ID: {$uv->id} | Matiere ID: {$uv->matiere_id} | UV Slug: '{$uv->slug}' | Matiere Nom: " . ($uv->matiere->nom ?? 'N/A') . "\n";
}

echo "\n--- MATIERES ---\n";
$matieres = \App\Models\Matiere::all();
foreach ($matieres as $m) {
    $slug = \Illuminate\Support\Str::slug($m->nom);
    $count = \App\Models\Matiere::where('slug', $slug)->where('id', '!=', $m->id)->count();
    if ($count > 0) {
        $originalSlug = $slug;
        $i = 1;
        while (\App\Models\Matiere::where('slug', $slug)->where('id', '!=', $m->id)->count() > 0) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }
    }
    $m->slug = $slug;
    $m->save();
    echo "Updated Matiere ID: {$m->id} -> Slug: '{$m->slug}'\n";
}
