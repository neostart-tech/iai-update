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
		// Create
		Permission::create([
			'nom' => 'Ajouter une note à un étudiant',
			'description' => 'Ajouter une note à un étudiant'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les notes d\'un étudiant',
			'description' => 'Voir les notes d\'un étudiant'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les notes d\'un étudiant de sa salle',
			'description' => 'Voir les notes d\'un étudiant de sa salle'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir une note d\'un étudiant',
			'description' => 'Ajouter une note d\'un étudiant'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier une note à un étudiant',
			'description' => 'Ajouter une note à un étudiant'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier la note d\'un étudiant après un certain temps',
			'description' => 'Modifier la note d\'un étudiant après un certain temps'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer une note à un étudiant',
			'description' => 'Supprimer une note à un étudiant'
		]);
	}
}
