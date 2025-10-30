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
		// Create
		Permission::create([
			'nom' => 'Ajouter un évènement',
			'description' => 'Ajouter un évènement'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les évènements',
			'description' => 'Voir les évènements'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les évènement de sa salle',
			'description' => 'Voir les évènement de sa salle'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir un évènement',
			'description' => 'Ajouter un évènement'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier un évènement',
			'description' => 'Ajouter un évènement'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer un évènement',
			'description' => 'Supprimer un évènement'
		]);
	}
}
