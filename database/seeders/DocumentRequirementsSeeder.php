<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Niveau;
use App\Models\DocumentRequirement;

class DocumentRequirementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // On récupère tous les niveaux existants
        $niveaux = Niveau::all();

        if ($niveaux->isEmpty()) {
            return;
        }

        // Configuration par défaut : Ce qui était dans le frontend en dur
        $defaultDocs = [
            ['key' => 'lettre', 'name' => 'Lettre de recommandation'],
            ['key' => 'naissance', 'name' => 'Acte de naissance'],
            ['key' => 'diplome', 'name' => 'Diplôme'],
            ['key' => 'nationalite', 'name' => 'Certificat de nationalité'],
            ['key' => 'photo', 'name' => "Photo d'identité"],
            ['key' => 'certificat_medical', 'name' => 'Certificat médical'],
            ['key' => 'cv', 'name' => 'Curriculum Vitae'],
            ['key' => 'lettre_motivation', 'name' => 'Lettre de motivation'],
        ];

        // On assigne par défaut ces documents à tous les niveaux pour ne rien casser
        // L'administration pourra ensuite ajuster depuis l'interface
        foreach ($niveaux as $niveau) {
            foreach ($defaultDocs as $doc) {
                DocumentRequirement::firstOrCreate([
                    'niveau_id' => $niveau->id,
                    'document_key' => $doc['key'],
                ], [
                    'nom_affichage' => $doc['name'],
                    'is_obligatoire' => true,
                ]);
            }
            
            // Si c'est un Master (ex: nom contenant Master) ou niveau 4/5, on peut ajouter relevé L1, L2, L3...
            // Mais pour faire simple et correspondre à l'existant, on va rajouter les relevés bac1 bac2
            // s'ils étaient dans le code dur du frontend.
            DocumentRequirement::firstOrCreate([
                'niveau_id' => $niveau->id,
                'document_key' => 'releve_bac1_path',
            ], [
                'nom_affichage' => 'Relevé de notes BAC 1',
                'is_obligatoire' => true,
            ]);

            DocumentRequirement::firstOrCreate([
                'niveau_id' => $niveau->id,
                'document_key' => 'releve_bac2_path',
            ], [
                'nom_affichage' => 'Relevé de notes BAC 2',
                'is_obligatoire' => true,
            ]);
        }
    }
}
