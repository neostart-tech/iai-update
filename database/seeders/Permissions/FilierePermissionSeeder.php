<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FilierePermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter une filière', 'description' => 'Ajouter une filière'],
			['nom' => 'Voir les filières', 'description' => 'Voir les filières'],
			['nom' => 'Voir une filière', 'description' => 'Voir une filière'],
			['nom' => 'Modifier une filière', 'description' => 'Modifier une filière'],
			['nom' => 'Supprimer une filière', 'description' => 'Supprimer une filière'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
