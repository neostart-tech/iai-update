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
		// Create
		Permission::create([
			'nom' => 'Ajouter une filière',
			'description' => 'Ajouter une filière'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les filières',
			'description' => 'Voir les filières'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir une filière',
			'description' => 'Ajouter une filière'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier une filière',
			'description' => 'Ajouter une filière'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer une filière',
			'description' => 'Supprimer une filière'
		]);
	}
}
