<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnneeScolaireSeeder extends Seeder
{
	use WithoutModelEvents;
	public function run(): void
	{
		AnneeScolaire::query()->create([
			'nom' => "Année scolaire 2023-2024",
			'code' => 'as_2023_2024',
			'slug' => uniqid()
		]);
	}
}
