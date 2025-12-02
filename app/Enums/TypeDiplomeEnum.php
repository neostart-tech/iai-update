<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération TypeDiplomeEnum pour les dépôts de candidatures
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
enum TypeDiplomeEnum: string
{
	use EnumsValuesTrait;

	case BAC2 = 'BAC 2';
	case DUT = 'Diplôme universitaire de technologie';
	case BULLETINS_LYCEE ='Bulletins de lycée';
	case RELEVE_BAC1='Relevé du Bac 1';
	case RELEVE_BAC2='Relevé du Bac 2';
}
