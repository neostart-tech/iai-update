<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Syllabus;
use Illuminate\Support\Str;

$syllabuses = Syllabus::with('uniteValeur.matiere')->get();
$count = 0;
foreach($syllabuses as $s) {
    if (empty($s->slug)) {
        // Manually generate slug
        $baseName = $s->uniteValeur ? $s->uniteValeur->nom : 'syllabus-' . $s->id;
        $slug = Str::slug($baseName);
        if (empty($slug)) {
            $slug = 'syllabus-' . $s->id;
        }
        
        // Ensure uniqueness manually
        $originalSlug = $slug;
        $i = 1;
        while (Syllabus::where('slug', $slug)->where('id', '!=', $s->id)->exists()) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        Syllabus::where('id', $s->id)->update(['slug' => $slug]);
        echo "Generated slug for Syllabus ID " . $s->id . ": " . $slug . "\n";
        $count++;
    }
}
echo "Updated $count syllabuses.\n";
