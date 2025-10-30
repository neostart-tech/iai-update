<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération TypeContratEnum pour les opportunités
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
enum TypeContratEnum: string
{
	use EnumsValuesTrait;

	case CDD = 'Contrat à Durée Déterminée';
	case CDI = 'Contrat à Durée Indéterminée';
	case ALTERNANCE = 'Alternance';
}
