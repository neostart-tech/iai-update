<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteVitrineConfigPermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter une configuration de site vitrine', 'description' => 'Ajouter une configuration de site vitrine'],
			['nom' => 'Voir la liste des configurations de site vitrine', 'description' => 'Voir la liste des configurations de site vitrine'],
			['nom' => 'Voir les emploi du temp de sa salle', 'description' => 'Voir les emploi du temp de sa salle'],
			['nom' => 'Voir une configuration de site vitrine', 'description' => 'Ajouter une configuration de site vitrine'],
			['nom' => 'Modifier une configuration de site vitrine', 'description' => 'Ajouter une configuration de site vitrine'],
			['nom' => 'Supprimer une configuration de site vitrine', 'description' => 'Supprimer une configuration de site vitrine'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
