<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotePermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		$permissions = [
			['nom' => 'Ajouter une note', 'description' => 'Ajouter une note'],
			['nom' => 'Voir les notes', 'description' => 'Voir les notes'],
			['nom' => 'Voir les notes de sa salle', 'description' => 'Voir les notes de sa salle'],
			['nom' => 'Voir une note', 'description' => 'Ajouter une note'],
			['nom' => 'Modifier une note', 'description' => 'Ajouter une note'],
			['nom' => 'Supprimer une note', 'description' => 'Supprimer une note'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
