<?php

namespace Database\Seeders\Permissions;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class AutresPermissionsSeeder extends Seeder
{
	public function run(): void
	{
		$permissions = [
			[
				'nom' => 'Voir la liste des payements de ses enfants',
				'description' => 'Voir la liste des payements de ses enfants',
			],
			[
				'nom' => 'Modifier le profil d\'un autre utilisateur',
				'description' => 'Modifier le profil d\'un autre utilisateur',
			],
		];

		foreach ($permissions as $p) {
			Permission::firstOrCreate(['nom' => $p['nom']], $p);
		}
	}
}
