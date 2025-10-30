<?php

use App\Models\CurrentEnv;

/**
 * Injecte l'identifiant de l'année scolaire dans un tableau
 *
 * @return array
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
if (!function_exists('injectAnneeScolaireId')) {
	function injectAnneeScolaireId(): array
	{
		return ['annee_scolaire_id' => getAnneeScolaireId()];
	}
}

/**
 * Obtient l'identifiant de l'année scolaire courante
 *
 * @return int
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
if (!function_exists('getAnneeScolaireId')) {
	function getAnneeScolaireId(): int
	{
		return CurrentEnv::getAnneeScolaireId();
	}
}
