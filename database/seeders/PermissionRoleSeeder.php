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
		/**
		 * @var Role $enseignant
		 */
		// Role Enseignant
		$enseignant = Role::query()->find(2);
		$enseignant->permissions()->attach([
			22, 23, 24, 25
		]);

		/**
		 * @var Role $de
		 */
		// Role Directeur des Etudes
		$de = Role::query()->find(5);
		$de->permissions()->attach([
			18, 19, 20, 21, 33
		]);

		/**
		 * @var Role $daas
		 */
		// Role Directeur des Affaires Académiques et Scolaires
		$daas = Role::query()->find(6);
		$daas->permissions()->attach([
			1, 4, 5
		]);

		/**
		 * @var Role $daf
		 */
		// Role Directeur des Affaires Financières
		$daf = Role::query()->find(7);
		$daf->permissions()->attach([
			52, 53, 55
		]);

		/**
		 * @var Role $surveillant
		 */
		// Role Surveillant.e
		$surveillant = Role::query()->find(9);
		$surveillant->permissions()->attach([
			23, 25
		]);

		/**
		 * @var Role $titulaire
		 */
		$titulaire = Role::query()->find(11);
		$titulaire->permissions()->attach([
			18, 19, 24, 30, 31, 36, 38
		]);

		/**
		 * @var Role $comiteEtudiant
		 */
		$comiteEtudiant = Role::query()->find(12);
		$comiteEtudiant->permissions()->attach([
			35, 37, 39, 40
		]);

		/**
		 * @var Role $etudiant
		 */
		$etudiant = Role::query()->find(1);
		$etudiant->permissions()->attach([
			18, 19, 31, 37, 38, 54
		]);

		/**
		 * @var Role $parent
		 */
		$parent = Role::query()->find(3);
		$parent->permissions()->attach([
			61
		]);

		/**
		 * @var Role $dg
		 */
		$dg = Role::query()->find(4);
		$dg->permissions()->attach([
			11, 14, 15, 19, 23, 35, 50, 53, 58
		]);

		/**
		 * @var Role $user
		 */
		$user = Role::query()->find(13);
		$user->permissions()->attach([
			2, 3, 7, 8, 12, 13, 17, 36, 38, 47, 57, 58
		]);

		/**
		 * @var Role $administrateurDuSite
		 */
		$administrateurDuSite = Role::query()->find(14);
		$administrateurDuSite->permissions()->attach([
			6, 41, 42, 43, 44, 56, 60, 62
		]);
	}
}
