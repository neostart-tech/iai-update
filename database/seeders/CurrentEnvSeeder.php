<?php

namespace Database\Seeders;

use App\Models\CurrentEnv;
use Illuminate\Database\Seeder;

class CurrentEnvSeeder extends Seeder
{
	public function run(): void
	{
		CurrentEnv::firstOrCreate(
			['nom' => 'annee_scolaire_id'],
			['valeur' => 1]
		);
	}
}
