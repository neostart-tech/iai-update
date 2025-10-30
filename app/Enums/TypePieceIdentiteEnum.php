<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération TypePieceIdentiteEnum pour les dépôts de candidature
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
enum TypePieceIdentiteEnum: string
{
	use EnumsValuesTrait;

	case CNI = "Carte Nationale Identité";
	case PASSEPORT = 'Passeport';
}
