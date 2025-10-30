<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération GenreEnum
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
enum GenreEnum: string
{
	use EnumsValuesTrait; // Utilisation d'un trait pour gérer les valeurs de l'énumération

	// Définition des valeurs de l'énumération avec leurs représentations
	case M = 'Masculin';
	case F = 'Féminin';

	// Méthode pour saluer en fonction du genre
	public function greeting(): string
	{
		return $this == self::M ? 'M.' : 'Mme/Mlle';
	}

	// Méthode pour saluer avec le titre complet
	public function fullGreeting(bool $isMadame = false): string
	{
		// Retourne 'Monsieur ' si le genre est Masculin, sinon 'Madame ' ou 'Mademoiselle '
		return $this === self::M ? 'Monsieur ' : ($isMadame ? 'Madame ' : 'Mademoiselle ');
	}
}
