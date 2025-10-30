<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération TypeProgrammeEnum
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
enum TypeProgrammeEnum: string
{
	use EnumsValuesTrait;

	case COURS = 'Cours';
	case EVALUATION = 'Évaluation';
	case EVENEMENT = 'Événement';
}
