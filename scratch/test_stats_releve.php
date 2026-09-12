<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\StatistiquesController();
$request = \Illuminate\Http\Request::create('/api/statistiques/evaluations/stats', 'GET');
echo "API response: " . $controller->fetchEvaluationsStats($request)->getContent() . "\n";
