<?php

namespace Database\Seeders;

use App\Enums\GenreEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run()
	{
		$user = User::firstOrCreate(
			['email' => 'admin@test.com'],
			[
				'nom' => 'Admin',
				'prenom' => 'Administrateur',
				'password' => Hash::make('password'),
				'genre' => GenreEnum::M->value,
				'image' => config('images.teachers.man'),
				'matricule' => Str::upper(Str::random(8)),
				'slug' => (string) Str::uuid(),
				'tel' => '00000000'
			]
		);

		$user->roles()->syncWithoutDetaching([13, 14]);
	}
}