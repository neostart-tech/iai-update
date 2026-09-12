<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
	public function run(): void
	{
		$roles = [
			'Etudiant',
			'Enseignant',
			'Parent',
			'Directeur Général',
			'Chargé des études et de la scolarité',
			'Directeur des Affaires Académique et Scolaires',
			'Directeur des Affaires Financières',
			'Secrétaires',
			'Surveillant',
			'Chargé de la Reprographie',
			'Titulaire d\'une classe',
			'Membre du comité Étudiant',
			'Utilisateur de la plateforme',
			'Responsable du site',
			'Delegue',
		];

		foreach ($roles as $roleNom) {
			Role::firstOrCreate([
				'nom' => $roleNom
			]);
		}
	}
}
