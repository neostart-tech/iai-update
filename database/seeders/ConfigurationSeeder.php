<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            [
                'key' => 'Nom de l\'établissement',
                'value' => 'ESCEN - École Supérieure'
            ],
            [
                'key' => 'Logo de l\'établissement',
                'value' => 'logo-default.png'
            ],
            [
                'key' => 'Titre du Chargé des études et de la scolarité',
                'value' => 'Directeur des Études'
            ],
            [
                'key' => 'Nom complet du Chargé des études et de la scolarité',
                'value' => 'M. DUPONT Jean-Pierre'
            ]
        ];

        foreach ($configurations as $config) {
            Configuration::updateOrCreate(
                ['key' => $config['key']],
                ['value' => $config['value']]
            );
        }
    }
}