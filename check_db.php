<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Evaluation;
use App\Models\ExamSubmission;

$slug = '69d8dec3cfe34';
$etudiantId = 1898;

$evaluation = Evaluation::where('slug', $slug)->first();

echo "Evaluation Slug: " . $slug . "\n";
if ($evaluation) {
    echo "ID Evaluation: " . $evaluation->id . "\n";
    echo "Titre: " . $evaluation->titre . "\n";
    
    $count = ExamSubmission::where('evaluation_id', $evaluation->id)
                           ->where('etudiant_id', $etudiantId)
                           ->count();
    
    echo "Soumissions pour cet étudiant: " . $count . "\n";
    
    $all = ExamSubmission::where('evaluation_id', $evaluation->id)
                           ->where('etudiant_id', $etudiantId)
                           ->get();
    foreach ($all as $s) {
        echo "- Question ID: " . $s->question_id . " | Créé le: " . $s->created_at . "\n";
    }
} else {
    echo "Evaluation non trouvée pour ce slug.\n";
}
