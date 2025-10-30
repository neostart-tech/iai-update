<?php

namespace Database\Seeders;

use App\Models\CurrentEnv;
use App\Models\Periode;
use Illuminate\Database\Seeder;

class PeriodeSeeder extends Seeder
{
	public function run(): void
	{
		Periode::create([
			'nom' => 'Semestre 1 de Licence',
			... injectAnneeScolaireId()
		]);

		Periode::create([
			'nom' => 'Semestre 2 de Licence',
			... injectAnneeScolaireId()
		]);

		Periode::create([
			'nom' => 'Semestre 3 de Licence',
			... injectAnneeScolaireId()
		]);

		Periode::create([
			'nom' => 'Semestre 4 de Licence',
			... injectAnneeScolaireId()
		]);

		Periode::create([
			'nom' => 'Semestre 5 de Licence',
			... injectAnneeScolaireId()
		]);

		Periode::create([
			'nom' => 'Semestre 6 de Licence',
			... injectAnneeScolaireId()
		]);
	}
}
