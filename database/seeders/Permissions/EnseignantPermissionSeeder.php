<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnseignantPermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		// Create
		Permission::create([
			'nom' => 'Ajouter un membre du personnel',
			'description' => 'Ajouter un membre du personnel'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les membres du personnel',
			'description' => 'Voir les membres du personnel'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir un membre du personnel',
			'description' => 'Voir un membre du personnel'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier un membre du personnel',
			'description' => 'Ajouter un membre du personnel'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer un membre du personnel',
			'description' => 'Supprimer un membre du personnel'
		]);
	}
}
