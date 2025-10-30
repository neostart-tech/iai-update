<?php

namespace Database\Seeders;

use Database\Seeders\Permissions\AutresPermissionsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	public function run(): void
	{
		$this->call([
			RoleSeeder::class,
			AnneeScolaireSeeder::class,
			CurrentEnvSeeder::class,
//			PeriodeSeeder::class,
			FiliereSeeder::class,
			PermissionSeeder::class,
			PermissionRoleSeeder::class,
			AutresPermissionsSeeder::class,
			AdminSeeder::class,
			// CandidatureSeeder::class
		]);
	}
}
