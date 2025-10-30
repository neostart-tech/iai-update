<?php

namespace Database\Seeders;

use App\Enums\GenreEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Hash};
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
	use WithoutModelEvents;

	public function run()
	{
		$user = User::create([
			'nom' => 'Admin',
			'prenom' => 'Administrateur',
			'password' => Hash::make('password'),
			'genre' => GenreEnum::M->value,
			'email' => 'admin@test.com',
			'image' => config('images.teachers.man'),
			'matricule' => Str::upper(Str::random(8)),
			'slug' => uniqid(),
			'tel' => '00000000'
		]);

		$user->roles()->attach([13, 14]);
	}
}