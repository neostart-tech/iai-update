<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FicheDePresencePermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		// Create
		Permission::create([
			'nom' => 'Ajouter une fiche de présence',
			'description' => 'Ajouter une fiche de présence'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les fiches de présence',
			'description' => 'Voir les fiches de présence'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les fiches de présence de sa salle',
			'description' => 'Voir les fiches de présence de sa salle'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir une fiche de présence',
			'description' => 'Ajouter une fiche de présence'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier une fiche de présence',
			'description' => 'Ajouter une fiche de présence'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer une fiche de présence',
			'description' => 'Supprimer une fiche de présence'
		]);
	}
}
