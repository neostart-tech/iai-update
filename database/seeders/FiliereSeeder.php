<?php

namespace Database\Seeders;

use App\Models\CurrentEnv;
use App\Models\Filiere;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FiliereSeeder extends Seeder
{
	public function run(): void
	{
		Filiere::create([
			'nom' => 'Génie Logiciel & Systèmes d\'Informations',
			'code' => 'GLSI',
			'description' => 'À fournir',
			'image' => config('images.filieres.default'),
			'annee_scolaire_id' => CurrentEnv::getAnneeScolaireId()
		]);

		Filiere::create([
			'nom' => 'Administration Systèmes & Réseaux',
			'code' => 'ASR',
			'description' => 'À fournir',
			'image' => config('images.filieres.default'),
			'annee_scolaire_id' => CurrentEnv::getAnneeScolaireId()
		]);

		Filiere::create([
			'nom' => 'Multimédia, Technologies Web & Infographie',
			'code' => 'MTWI',
			'description' => 'À fournir',
			'image' => config('images.filieres.default'),
			'annee_scolaire_id' => CurrentEnv::getAnneeScolaireId()
		]);
	}
}
