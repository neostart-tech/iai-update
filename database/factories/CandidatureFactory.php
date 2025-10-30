<?php

namespace Database\Factories;

use App\Enums\GenreEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CandidatureFactory extends Factory
{
	private const VILLES = ['Lomé', 'Dapaong', 'Aného', 'Bè', 'Kara', 'Atakpamé', 'Sokodé'];

	private const NATIONALITES = ['Togo', 'France', 'Burkina-Faso'];

	public function definition(): array
	{
		$nomJeuneFille = null;
		$goodAge = today()->subYears(18);
		/**
		 * @var GenreEnum $genre
		 */
		$genre = fake()->randomElement(GenreEnum::cases());
		$gender = 'male';

		if ($genre === GenreEnum::F) {
			$gender = 'female';
			$nomJeuneFille = fake()->firstName($gender);
		}

		return [
			'nom' => $nom = fake()->lastName($gender),
			'prenom' => $prenom = fake()->firstName($gender),
			'nom_jeune_fille' => $nomJeuneFille,
			'genre' => $genre->value,
			'date_naissance' => fake()->date(max: $goodAge->toString()),
			'lieu_naissance' => fake()->randomElement(static::VILLES),
			'nationalite' => fake()->randomElement(static::NATIONALITES),
			'tel' => '+228 ' . Str::substr(fake()->unique()->e164PhoneNumber(), 3),
			'hobbit' => fake()->paragraph(3),
			'email' => fake()->unique()->email(),
			'password' => Hash::make('password'),
			'annee_scolaire_id' => getAnneeScolaireId(),
			'code' => fake()->unique()->numberBetween(9999, 100000),
//			'slug' => uniqid($nom . '-' . $prenom)
		];
	}
}
