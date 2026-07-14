<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$reqs = \App\Models\DocumentRequirement::all();
$albums = \App\Models\Album::latest()->take(5)->get();

echo json_encode(['requirements' => $reqs, 'albums' => $albums]);
