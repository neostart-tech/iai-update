<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteVitrineConfigPermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		// Create
		Permission::create([
			'nom' => 'Ajouter un élément au site publique',
			'description' => 'Ajouter un élément au site publique'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les éléments du site publique',
			'description' => 'Voir les éléments du site publique'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir un élément au site publique',
			'description' => 'Ajouter un élément au site publique'
		]);

		// Update
		Permission::create([
			'nom' => 'Modifier un élément au site publique',
			'description' => 'Ajouter un élément au site publique'
		]);

		// Delete
		Permission::create([
			'nom' => 'Supprimer un élément au site publique',
			'description' => 'Supprimer un élément au site publique'
		]);
	}
}
