<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération TypeAnnonceEnum
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
enum TypeAnnonceEnum: string
{
	use EnumsValuesTrait; // Utilisation d'un trait pour gérer les valeurs de l'énumération

	// Définition des valeurs de l'énumération avec leurs représentations
	case FULL_TIME = 'Plein temps';
	case HALF_TIME = 'Temps partiel';
	case REMOTE = 'Télétravail';
}
