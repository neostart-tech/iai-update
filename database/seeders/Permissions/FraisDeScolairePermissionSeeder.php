<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FraisDeScolairePermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter un frais scolaire', 'description' => 'Ajouter un frais scolaire'],
			['nom' => 'Voir la liste des frais scolaire', 'description' => 'Voir la liste des frais scolaire'],
			['nom' => 'Voir les emploi du temp de sa salle', 'description' => 'Voir les emploi du temp de sa salle'],
			['nom' => 'Voir un frais scolaire', 'description' => 'Ajouter un frais scolaire'],
			['nom' => 'Modifier un frais scolaire', 'description' => 'Ajouter un frais scolaire'],
			['nom' => 'Supprimer un frais scolaire', 'description' => 'Supprimer un frais scolaire'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
