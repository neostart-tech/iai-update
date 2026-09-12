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
            // Informations Établissement & Relevés de notes
            [
                'key' => 'ministere_tutelle',
                'value' => 'MINISTERE DE L\'ENSEIGNEMENT<br>SUPERIEUR ET DE LA RECHERCHE',
                'type' => 'textarea',
            ],
            [
                'key' => 'republique',
                'value' => 'REPUBLIQUE TOGOLAISE',
                'type' => 'text',
            ],
            [
                'key' => 'devise',
                'value' => 'Travail - Liberté - Patrie',
                'type' => 'text',
            ],
            [
                'key' => 'agrement',
                'value' => 'N° 0102/2021/MESR/SG/DES',
                'type' => 'text',
            ],
            [
                'key' => 'nom_de_etablissement',
                'value' => 'ESCEN - École Supérieure',
                'type' => 'text',
            ],
            [
                'key' => 'Nom de l\'établissement',
                'value' => 'ESCEN - École Supérieure',
                'type' => 'text',
            ],
            [
                'key' => 'sigle_etablissement',
                'value' => 'ESCEN',
                'type' => 'text',
            ],
            [
                'key' => 'logo_etablissement',
                'value' => 'logo-default.png',
                'type' => 'file',
            ],
            [
                'key' => 'Logo de l\'établissement',
                'value' => 'logo-default.png',
                'type' => 'file',
            ],
            [
                'key' => 'titre_du_directeur_des_etudes',
                'value' => 'Directeur des Études',
                'type' => 'text',
            ],
            [
                'key' => 'Titre du Chargé des études et de la scolarité',
                'value' => 'Directeur des Études',
                'type' => 'text',
            ],
            [
                'key' => 'nom_complet_du_directeur_des_etudes',
                'value' => 'M. DUPONT Jean-Pierre',
                'type' => 'text',
            ],
            [
                'key' => 'Nom complet du Chargé des études et de la scolarité',
                'value' => 'M. DUPONT Jean-Pierre',
                'type' => 'text',
            ],
            // Coordonnées & Contacts
            [
                'key' => 'adresse_physique',
                'value' => 'Tokoin Wuiti, Lomé - Togo',
                'type' => 'text',
            ],
            [
                'key' => 'telephone',
                'value' => '(228) 98 01 27 27 / 92 30 87 87',
                'type' => 'text',
            ],
            [
                'key' => 'email_contact',
                'value' => 'hello@escen.university',
                'type' => 'text',
            ],
            [
                'key' => 'email_admission',
                'value' => 'admission@escen.university',
                'type' => 'text',
            ],
            [
                'key' => 'website_url',
                'value' => 'https://escen.university',
                'type' => 'text',
            ],
            [
                'key' => 'URL du site web',
                'value' => 'https://escen.university',
                'type' => 'text',
            ],
            // Pédagogie & Candidatures
            [
                'key' => 'examens_uniquement',
                'value' => '0',
                'type' => 'boolean',
            ],
            [
                'key' => 'mode_selection_candidats',
                'value' => 'dossier',
                'type' => 'select',
                'options' => 'dossier|Dossier uniquement (dépôt / étude / inscription finale),concours|Concours avec épreuve écrite (paiement / présence / notes / admission)'
            ],
            [
                'key' => 'matricule_prefix',
                'value' => 'ESC',
                'type' => 'text',
            ],
            [
                'key' => 'email_domain',
                'value' => 'escen.university',
                'type' => 'text',
            ],
            // Statistiques Communication
            [
                'key' => 'stat_etudiants_formes',
                'value' => '520',
                'type' => 'text',
            ],
            [
                'key' => 'stat_diplomes',
                'value' => '410',
                'type' => 'text',
            ],
            [
                'key' => 'stat_partenaires',
                'value' => '84',
                'type' => 'text',
            ],
            [
                'key' => 'stat_insertion_pro',
                'value' => '76',
                'type' => 'text',
            ]
        ];

        foreach ($configurations as $config) {
            $data = [
                'value' => $config['value'],
            ];
            if (isset($config['type'])) $data['type'] = $config['type'];
            if (isset($config['options'])) $data['options'] = $config['options'];

            Configuration::updateOrCreate(
                ['key' => $config['key']],
                $data
            );
        }
    }
}