<?php

namespace App\Enums;

use App\Traits\EnumsValuesTrait;

/**
 * Définition de l'énumération ModeFormationEnum
 * @package IAI-SITE
 */
enum ModeFormationEnum: string
{
    use EnumsValuesTrait;

    case PRESENTIEL = 'Présentiel';
    case EN_LIGNE = 'En ligne';
    case TOUS = 'Tous';
}
