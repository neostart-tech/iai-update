<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rules = [
    'salle' => ['required_without:salle_id'],
    'salle_id' => ['required_without:salle']
];

$messages = [
    'salle_id.required_without' => 'La salle est obligatoire.',
    'salle.required_without' => 'La salle est obligatoire.'
];

$validator = validator([], $rules, $messages);

echo json_encode([
    'fails' => $validator->fails(),
    'error' => $validator->errors()->first()
]);
