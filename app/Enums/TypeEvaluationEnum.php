<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération TypeEvaluationEnum
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
enum TypeEvaluationEnum: string
{
	use EnumsValuesTrait;

	case DEVOIR = 'Devoir';
	case EXAMEN = 'Examen';
	case INTERRO = 'Interrogation';
    case TP = 'TP';
    case EXPOSE = 'Exposé';
}
