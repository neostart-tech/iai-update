<?php

namespace Database\Seeders;

use App\Models\{Album, Candidature, ResponsableFrais, Tuteur};
use Illuminate\Database\Seeder;

class CandidatureSeeder extends Seeder
{
	public function run(): void
	{
		Candidature::factory()
			->state([
				'dossier_valide' => true,
				'validation_date' => fake()->date,
				'frais_paye' => true,
				'frai_paye_date' => fake()->date,
				'participation' => true,
				'participation_date' => fake()->date,
				'admission' => true,
				'admission_date' => fake()->date,
			])
			->count(10)
			->has(Album::factory())
			->has(Tuteur::factory())
			->has(ResponsableFrais::factory(), 'responsable')
			->create();
	}
}
