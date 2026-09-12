<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmploiDuTempPermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter un emploi du temps', 'description' => 'Ajouter un emploi du temps'],
			['nom' => 'Voir les emploi du temp', 'description' => 'Voir les emploi du temp'],
			['nom' => 'Voir les emploi du temp de sa salle', 'description' => 'Voir les emploi du temp de sa salle'],
			['nom' => 'Voir un emploi du temps', 'description' => 'Ajouter un emploi du temps'],
			['nom' => 'Modifier un emploi du temps', 'description' => 'Ajouter un emploi du temps'],
			['nom' => 'Supprimer un emploi du temps', 'description' => 'Supprimer un emploi du temps'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
