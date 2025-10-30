<?php

namespace Database\Factories;

use App\Enums\GenreEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TuteurFactory extends Factory
{
	public function definition(): array
	{
		$gender = 'male';

		if (fake()->randomElement(GenreEnum::cases()) === GenreEnum::F) {
			$gender = 'female';
		}

		return [
			'nom' => fake()->lastName($gender),
			'prenom' => fake()->firstName($gender),
			'email' => fake()->unique()->email(),
			'profession' => fake()->word,
			'employeur' => fake()->word,
			'adresse' => fake()->address,
			'tel' => '+228 ' . Str::substr(fake()->unique()->e164PhoneNumber, 3),
		];
	}
}
