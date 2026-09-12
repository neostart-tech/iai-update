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
		$permissions = [
			['nom' => 'Ajouter un candidat entrant', 'description' => 'Ajouter un candidat entrant'],
			['nom' => 'Voir la liste des candidats', 'description' => 'Voir la liste des candidats'],
			['nom' => 'Voir les emploi du temp de sa salle', 'description' => 'Voir les emploi du temp de sa salle'],
			['nom' => 'Voir un candidat entrant', 'description' => 'Ajouter un candidat entrant'],
			['nom' => 'Modifier un candidat entrant', 'description' => 'Ajouter un candidat entrant'],
			['nom' => 'Supprimer un candidat entrant', 'description' => 'Supprimer un candidat entrant'],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
