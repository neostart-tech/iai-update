<?php

namespace Database\Seeders;

use App\Models\CurrentEnv;
use Illuminate\Database\Seeder;

class CurrentEnvSeeder extends Seeder
{
	public function run(): void
	{
		CurrentEnv::create([
			'nom' => 'annee_scolaire_id',
			'valeur' => 1,
		]);
	}
}
