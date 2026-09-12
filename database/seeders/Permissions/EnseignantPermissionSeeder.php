<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnseignantPermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter un enseignant', 'description' => 'Ajouter un enseignant'],
			['nom' => 'Voir les enseignants', 'description' => 'Voir les enseignants'],
			['nom' => 'Voir un enseignant', 'description' => 'Ajouter un enseignant'],
			['nom' => 'Modifier un enseignant', 'description' => 'Ajouter un enseignant'],
			['nom' => 'Supprimer un enseignant', 'description' => 'Supprimer un enseignant'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
