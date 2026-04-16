<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Evaluation;
use App\Models\ExamPart;
use App\Models\ExamQuestion;

$slug = '69d8ec1e7f0c5';
$evaluation = Evaluation::where('slug', $slug)->first();

if (!$evaluation) {
    echo "Evaluation not found for slug: $slug\n";
    exit;
}

echo "Evaluation Found: " . $evaluation->matiere->nom . " (ID: " . $evaluation->id . ")\n";

$parts = ExamPart::where('evaluation_id', $evaluation->id)->get();
echo "Number of Parts: " . $parts->count() . "\n";

foreach ($parts as $part) {
    echo "  Part ID: " . $part->id . " - " . $part->titre . "\n";
    $questions = ExamQuestion::where('part_id', $part->id)->get();
    echo "    Number of Questions: " . $questions->count() . "\n";
}
