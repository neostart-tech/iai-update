<?php

namespace App\Traits;

trait UserIdentityTrait
{
	public function greeting(bool $basedOnTime = false): string
	{
		return ($basedOnTime ? getGreetingTime() : "Bonjour ") .
			$this->getAttribute('genre')->greeting() . ' ' . $this->completName();
	}

	public function completName(): string
	{
		return $this->getAttribute('nom') . ' ' . $this->getAttribute('prenom');
	}
}
