<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SallePermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter une salle', 'description' => 'Ajouter une salle'],
			['nom' => 'Voir les salles', 'description' => 'Voir les salles'],
			['nom' => 'Voir une salle', 'description' => 'Voir une salle'],
			['nom' => 'Modifier une salle', 'description' => 'Modifier une salle'],
			['nom' => 'Supprimer une salle', 'description' => 'Supprimer une salle'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
