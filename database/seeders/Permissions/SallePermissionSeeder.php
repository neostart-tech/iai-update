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
		// Create
		Permission::create([
			'nom' => 'Ajouter une salle',
			'description' => 'Ajouter une salle'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les salles',
			'description' => 'Voir les salles'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir une salle',
			'description' => 'Ajouter une salle'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier une salle',
			'description' => 'Ajouter une salle'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer une salle',
			'description' => 'Supprimer une salle'
		]);
	}
}
