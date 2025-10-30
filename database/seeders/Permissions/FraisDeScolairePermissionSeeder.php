<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FraisDeScolairePermissionSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		// Create
		Permission::create([
			'nom' => 'Ajouter un enregistrement de payement de frais de scolarité',
			'description' => 'Ajouter un enregistrement de payement de frais de scolarité'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir les enregistrements de payement de frais de scolarité',
			'description' => 'Voir les enregistrements de payement de frais de scolarité'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir ses enregistrements de payement de frais de scolarité',
			'description' => 'Voir ses enregistrements de payement de frais de scolarité'
		]);

		// Read
		Permission::create([
			'nom' => 'Voir un enregistrement de payement de frais de scolarité',
			'description' => 'Ajouter un enregistrement de payement de frais de scolarité'
		]);
	}
}
