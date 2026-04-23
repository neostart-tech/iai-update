<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$note = \App\Models\Note::withoutGlobalScopes()->latest('updated_at')->first();
if ($note) {
    echo "Note ID: " . $note->id . "\n";
    echo "Note Value: " . $note->note . "\n";
    echo "Notation: " . $note->notation . "\n";
    echo "Updated At: " . $note->updated_at . "\n";
} else {
    echo "No notes found.\n";
}
