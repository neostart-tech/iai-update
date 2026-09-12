<?php

namespace Database\Seeders;

use App\Models\Periode;
use Illuminate\Database\Seeder;

class PeriodeSeeder extends Seeder
{
	public function run(): void
	{
		$periodes = [
			'Semestre 1 de Licence',
			'Semestre 2 de Licence',
			'Semestre 3 de Licence',
			'Semestre 4 de Licence',
			'Semestre 5 de Licence',
			'Semestre 6 de Licence',
		];

		foreach ($periodes as $nom) {
			Periode::firstOrCreate(
				['nom' => $nom],
				injectAnneeScolaireId()
			);
		}
	}
}
