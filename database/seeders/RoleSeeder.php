<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
	public function run(): void
	{
		Role::create([
			'nom' => 'Etudiant'
		]);

		Role::create([
			'nom' => 'Enseignant'
		]);

		Role::create([
			'nom' => 'Parent'
		]);

		Role::create([
			'nom' => 'Directeur Général'
		]);

		Role::create([
			'nom' => 'Chargé des études et de la scolarité'
		]);

		Role::create([
			'nom' => 'Directeur des Affaires Académique et Scolaires'
		]);

		Role::create([
			'nom' => 'Directeur des Affaires Financières'
		]);

		Role::create([
			'nom' => 'Secrétaires'
		]);

		Role::create([
			'nom' => 'Surveillant'
		]);

		Role::create([
			'nom' => 'Chargé de la Reprographie'
		]);

		Role::create([
			'nom' => 'Titulaire d\'une classe'
		]);

		Role::create([
			'nom' => 'Membre du comité Étudiant'
		]);

		Role::create([
			'nom' => 'Utilisateur de la plateforme'
		]);

		Role::create([
			'nom' => 'Responsable du site'
		]);

//		Role::create([
//			'nom' => 'Responsable du site publique'
//		]);
	}
}
