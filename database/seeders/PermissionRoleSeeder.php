<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeder permettant d'attacher des permissions aux différents rôles (Profil)
 */
class PermissionRoleSeeder extends Seeder
{
	public function run(): void
	{
		$rolePermissionsMap = [
			'Etudiant' => [18, 19, 31, 37, 38, 54],
			'Enseignant' => [22, 23, 24, 25],
			'Parent' => [61],
			'Directeur Général' => [11, 14, 15, 19, 23, 35, 50, 53, 58],
			'Chargé des études et de la scolarité' => [18, 19, 20, 21, 33],
			'Directeur des Affaires Académique et Scolaires' => [1, 4, 5],
			'Directeur des Affaires Financières' => [52, 53, 55],
			'Surveillant' => [23, 25],
			'Titulaire d\'une classe' => [18, 19, 24, 30, 31, 36, 38],
			'Membre du comité Étudiant' => [35, 37, 39, 40],
			'Utilisateur de la plateforme' => [2, 3, 7, 8, 12, 13, 17, 36, 38, 47, 57, 58],
			'Responsable du site' => [6, 41, 42, 43, 44, 56, 60, 62],
		];

		foreach ($rolePermissionsMap as $roleNom => $permissionIds) {
			$role = Role::where('nom', $roleNom)->first();
			if ($role) {
				$role->permissions()->syncWithoutDetaching($permissionIds);
			}
		}
	}
}
