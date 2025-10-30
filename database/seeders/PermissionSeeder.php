<?php

namespace Database\Seeders;

use Database\Seeders\Permissions\{ActivePermissionSeeder,
	CandidatValidationPermissionSeeder,
	EmploiDuTempPermissionSeeder,
	EnseignantPermissionSeeder,
	FicheDePresencePermissionSeeder,
	FilierePermissionSeeder,
	FraisDeScolairePermissionSeeder,
	NotePermissionSeeder,
	SallePermissionSeeder,
	SiteVitrineConfigPermissionSeeder,
	UtilisateurPermissionSeeder};
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
	public function run(): void
	{
		$this->call([
			FilierePermissionSeeder::class,
			SallePermissionSeeder::class,
			EnseignantPermissionSeeder::class,
			EmploiDuTempPermissionSeeder::class,
			FicheDePresencePermissionSeeder::class,
			NotePermissionSeeder::class,
			ActivePermissionSeeder::class,
			SiteVitrineConfigPermissionSeeder::class,
			CandidatValidationPermissionSeeder::class,
			FraisDeScolairePermissionSeeder::class,
			UtilisateurPermissionSeeder::class
		]);
	}
}
