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
		$permissions = [
			['nom' => 'Ajouter une fiche de présence', 'description' => 'Ajouter une fiche de présence'],
			['nom' => 'Voir les fiche de présence', 'description' => 'Voir les fiche de présence'],
			['nom' => 'Voir les fiche de présence de sa salle', 'description' => 'Voir les fiche de présence de sa salle'],
			['nom' => 'Voir une fiche de présence', 'description' => 'Ajouter une fiche de présence'],
			['nom' => 'Modifier une fiche de présence', 'description' => 'Ajouter une fiche de présence'],
			['nom' => 'Supprimer une fiche de présence', 'description' => 'Supprimer une fiche de présence'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
