<?php

namespace App\Traits;

trait UserIdentityTrait
{
	public function greeting(bool $basedOnTime = false): string
	{
		$genre = $this->getAttribute('genre');
		$genreGreeting = $genre ? $genre->greeting() : '';
		return ($basedOnTime ? getGreetingTime() : "Bonjour ") .
			trim($genreGreeting . ' ' . $this->completName());
	}

	public function completName(): string
	{
		return $this->getAttribute('nom') . ' ' . $this->getAttribute('prenom');
	}
}
