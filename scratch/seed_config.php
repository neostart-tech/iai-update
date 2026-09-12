<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$configs = [
    ['key' => 'ministere_tutelle', 'value' => 'MINISTERE DE L\'ENSEIGNEMENT<br>SUPERIEUR ET DE LA RECHERCHE', 'type' => 'textarea'],
    ['key' => 'republique', 'value' => 'REPUBLIQUE TOGOLAISE', 'type' => 'text'],
    ['key' => 'devise', 'value' => 'Travail - Liberté - Patrie', 'type' => 'text'],
    ['key' => 'agrement', 'value' => 'N° 0102/2021/MESR/SG/DES', 'type' => 'text'],
    ['key' => 'adresse_physique', 'value' => 'Tokoin Wuiti', 'type' => 'text'],
    ['key' => 'telephone', 'value' => '(228) 98 01 27 27 / 92 30 87 87', 'type' => 'text'],
    ['key' => 'email_contact', 'value' => 'hello@escen.university', 'type' => 'text'],
    ['key' => 'email_admission', 'value' => 'admission@escen.university', 'type' => 'text']
];

foreach ($configs as $config) {
    \App\Models\Configuration::updateOrCreate(['key' => $config['key']], $config);
}
echo "Configurations inserted successfully.\n";
