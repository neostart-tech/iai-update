<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivePermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter un évènement', 'description' => 'Ajouter un évènement'],
			['nom' => 'Voir les évènements', 'description' => 'Voir les évènements'],
			['nom' => 'Voir les évènement de sa salle', 'description' => 'Voir les évènement de sa salle'],
			['nom' => 'Voir un évènement', 'description' => 'Ajouter un évènement'],
			['nom' => 'Modifier un évènement', 'description' => 'Ajouter un évènement'],
			['nom' => 'Supprimer un évènement', 'description' => 'Supprimer un évènement'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
