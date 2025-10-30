<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AutresPermissionsSeeder extends Seeder
{
	public function run(): void
	{
		Permission::create([
			'nom' => 'Voir la liste des payements de ses enfants',
			'description' => 'Voir la liste des payements de ses enfants',
		]);

		Permission::create([
			'nom' => 'Modifier le profil d\'un autre utilisateur',
			'description' => 'Modifier le profil d\'un autre utilisateur',
		]);
	}
}
