<?php
// scratch/check_eval.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Evaluation;
use App\Models\ExamPart;
use App\Models\ExamQuestion;

$slug = '69dce419c5b25'; // From the screenshot
$eval = Evaluation::withoutGlobalScopes()->where('slug', $slug)->first();

if (!$eval) {
    echo "Evaluation not found for slug: $slug\n";
    exit;
}

echo "Evaluation Found: " . $eval->titre . " (ID: " . $eval->id . ")\n";
echo "Date Debut: " . $eval->debut . "\n";
echo "Date Fin: " . $eval->fin . "\n";
echo "Annee Scolaire ID: " . $eval->annee_scolaire_id . "\n";
echo "Current Annee Scolaire ID (Global): " . getAnneeScolaireId() . "\n";

$parts = ExamPart::where('evaluation_id', $eval->id)->get();
echo "Number of parts: " . $parts->count() . "\n";

foreach ($parts as $part) {
    echo "  Part: " . $part->titre . " (ID: " . $part->id . ")\n";
    $questions = ExamQuestion::where('part_id', $part->id)->get();
    echo "    Number of questions: " . $questions->count() . "\n";
}
