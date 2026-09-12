<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnneeScolaireSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run(): void
	{
		AnneeScolaire::firstOrCreate(
			['code' => 'as_2023_2024'],
			[
				'nom' => 'Année scolaire 2023-2024',
				'slug' => (string) Str::uuid(),
				'active' => true,
			]
		);
	}
}
