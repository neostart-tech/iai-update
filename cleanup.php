<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Cleaning up...\n";

if (Schema::hasColumn('unite_valeurs', 'matiere_id')) {
    Schema::table('unite_valeurs', function(Blueprint $table) {
        $table->dropColumn('matiere_id');
    });
    echo "Dropped matiere_id column from unite_valeurs.\n";
}

if (Schema::hasTable('matieres')) {
    Schema::drop('matieres');
    echo "Dropped matieres table.\n";
}

echo "Cleanup done.\n";
