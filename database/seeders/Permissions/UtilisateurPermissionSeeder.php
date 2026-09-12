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
		$permissions = [
			['nom' => 'Ajouter un utilisateur', 'description' => 'Ajouter un utilisateur'],
			['nom' => 'Voir la liste des utilisateurs', 'description' => 'Voir la liste des utilisateurs'],
			['nom' => 'Voir les emploi du temp de sa salle', 'description' => 'Voir les emploi du temp de sa salle'],
			['nom' => 'Voir un utilisateur', 'description' => 'Ajouter un utilisateur'],
			['nom' => 'Modifier un utilisateur', 'description' => 'Ajouter un utilisateur'],
			['nom' => 'Supprimer un utilisateur', 'description' => 'Supprimer un utilisateur'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
