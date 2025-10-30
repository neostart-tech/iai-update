<?php

/**
 * Retourne un salut en fonction de l'heure actuelle
 *
 * @return string
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
if (!function_exists('getGreetingTime')) {
	function getGreetingTime(): string
	{
		return now()->isBefore(today()->setHour(12)) ? 'Bonjour ' : 'Bonsoir ';
	}
}
