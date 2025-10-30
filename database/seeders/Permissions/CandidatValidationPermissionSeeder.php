<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CandidatValidationPermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		// Create
		Permission::create([
			'nom' => 'Ajouter un candidat entrant',
			'description' => 'Ajouter un candidat entrant'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir la liste des candidats',
			'description' => 'Voir la liste des candidats'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les emploi du temp de sa salle',
			'description' => 'Voir les emploi du temp de sa salle'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir un candidat entrant',
			'description' => 'Ajouter un candidat entrant'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier un candidat entrant',
			'description' => 'Ajouter un candidat entrant'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer un candidat entrant',
			'description' => 'Supprimer un candidat entrant'
		]);
	}
}
