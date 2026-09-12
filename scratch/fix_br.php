<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$config = \App\Models\Configuration::where('key', 'ministere_tutelle')->first();
if ($config) {
    $config->value = str_replace('<br>', "\n", $config->value);
    $config->save();
    echo "Fixed line breaks in ministere_tutelle.\n";
}
