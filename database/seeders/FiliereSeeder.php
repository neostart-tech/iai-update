<?php

namespace Database\Seeders;

use App\Models\CurrentEnv;
use App\Models\Filiere;
use Illuminate\Database\Seeder;

class FiliereSeeder extends Seeder
{
	public function run(): void
	{
		$filieres = [
			[
				'code' => 'GLSI',
				'nom' => 'Génie Logiciel & Systèmes d\'Informations',
				'description' => 'À fournir',
				'image' => config('images.filieres.default'),
				'annee_scolaire_id' => CurrentEnv::getAnneeScolaireId()
			],
			[
				'code' => 'ASR',
				'nom' => 'Administration Systèmes & Réseaux',
				'description' => 'À fournir',
				'image' => config('images.filieres.default'),
				'annee_scolaire_id' => CurrentEnv::getAnneeScolaireId()
			],
			[
				'code' => 'MTWI',
				'nom' => 'Multimédia, Technologies Web & Infographie',
				'description' => 'À fournir',
				'image' => config('images.filieres.default'),
				'annee_scolaire_id' => CurrentEnv::getAnneeScolaireId()
			]
		];

		foreach ($filieres as $filiereData) {
			Filiere::firstOrCreate(
				['code' => $filiereData['code']],
				$filiereData
			);
		}
	}
}
