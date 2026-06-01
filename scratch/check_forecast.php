<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AnneeScolaire;
use App\Models\Echeance;
use Carbon\Carbon;

$active = AnneeScolaire::where('active', true)->first();
echo "Active Year: " . ($active ? $active->libelle : "None") . "\n";

$count = Echeance::where('date_limite', '>=', Carbon::now())->count();
echo "Upcoming Echeances Count: " . $count . "\n";

$all = Echeance::count();
echo "Total Echeances Count: " . $all . "\n";

if ($all > 0) {
    $latest = Echeance::orderByDesc('date_limite')->first();
    echo "Latest Echeance Date: " . $latest->date_limite . "\n";
}
