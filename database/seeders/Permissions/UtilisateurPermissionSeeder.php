<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UtilisateurPermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		// Create
		Permission::create([
			'nom' => 'Ajouter un utilisateur',
			'description' => 'Ajouter un utilisateur'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir un utilisateur',
			'description' => 'Voir un utilisateur'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir tous les utilisateurs',
			'description' => 'Voir tous les utilisateurs'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier un utilisateur',
			'description' => 'Ajouter un utilisateur'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer un utilisateur',
			'description' => 'Supprimer un utilisateur'
		]);
	}
}
