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
		// Create
		Permission::create([
			'nom' => 'Ajouter un emploi du temps',
			'description' => 'Ajouter un emploi du temps'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les emploi du temp',
			'description' => 'Voir les emploi du temp'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les emploi du temp de sa salle',
			'description' => 'Voir les emploi du temp de sa salle'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir un emploi du temps',
			'description' => 'Ajouter un emploi du temps'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier un emploi du temps',
			'description' => 'Ajouter un emploi du temps'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer un emploi du temps',
			'description' => 'Supprimer un emploi du temps'
		]);
	}
}
