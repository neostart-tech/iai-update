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

	case BAC2 = 'Bac 2';
	case BTS = 'BTS';
	case LICENCE = 'Licence';
	case MASTER = 'Master';
}
