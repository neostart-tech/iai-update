<?php

namespace Database\Factories;

use App\Enums\TypeDiplomeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
	public function definition(): array
	{
		return [
			'lettre' => fake()->filePath(),
			'naissance' => fake()->filePath(),
			'diplome' => fake()->filePath(),
			'nationalite' => fake()->filePath(),
			'photo' => fake()->filePath(),
			'type_diplome' => fake()->randomElement(TypeDiplomeEnum::values()),
			'certificat_medical' => fake()->filePath(),
			'coupon' => fake()->randomElement([null, fake()->filePath()]),
		];
	}
}
