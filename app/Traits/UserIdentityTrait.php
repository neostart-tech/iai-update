<?php

namespace App\Traits;

trait UserIdentityTrait
{
	public function greeting(bool $basedOnTime = false): string
	{
		return ($basedOnTime ? getGreetingTime() : "Bonjour ") .
			$this->civiliteName();
	}

	public function completName(): string
	{
		$nom = $this->getAttribute('nom');
		$prenom = $this->getAttribute('prenom');
		if (!empty($nom) || !empty($prenom)) {
			return trim($nom . ' ' . $prenom);
		}
		return $this->getAttribute('name') ?? '';
	}

	public function civiliteName(string $defaultTitle = 'M.'): string
	{
		$genre = $this->getAttribute('genre');
		$title = '';

		if ($genre instanceof \App\Enums\GenreEnum) {
			$title = $genre === \App\Enums\GenreEnum::M ? 'M.' : 'Mme';
		} elseif (is_string($genre) || is_numeric($genre)) {
			$g = strtoupper(trim((string)$genre));
			if (in_array($g, ['M', 'MASCULIN', 'MR', 'MONSIEUR', '1'])) {
				$title = 'M.';
			} elseif (in_array($g, ['F', 'FEMININ', 'MME', 'MADAME', '2'])) {
				$title = 'Mme';
			}
		}

		if (!$title) {
			$title = $defaultTitle;
		}

		$fullName = $this->completName();
		return trim($title . ' ' . $fullName);
	}
}
