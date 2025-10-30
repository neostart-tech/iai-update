<?php

use Illuminate\Support\Str;
use MercurySeries\Flashy\Flashy;

/**
 * Génère un message et le stocke en session
 *
 * @param string $message Le message à afficher
 * @param string|null $icon L'icône à utiliser
 * @param int|null $duration La durée d'affichage du message
 * @param string $key La clé sous laquelle stocker le message en session
 * @return string[] Un tableau vide
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
if (!function_exists('messageGenerator')) {
	function messageGenerator(string $message, string $icon = null, int $duration = 2800, string $key = 'success'): array
	{
		session()->push($key, $message);
//		Flashy::$key($message, icon: $icon, duration: 2800);
		return [];
	}
}

/**
 * Génère un message de succès
 *
 * @param string $message Le message à afficher
 * @param string|null $icon L'icône à utiliser
 * @param int|null $duration La durée d'affichage du message
 * @return array
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
if (!function_exists('successMsg')) {
	function successMsg(string $message, string $icon = null, int $duration = null): array
	{
		return messageGenerator(message: $message, icon: $icon);
	}
}

/**
 * Génère un message d'avertissement
 *
 * @param string $message Le message à afficher
 * @param int $duration La durée d'affichage du message
 * @return array
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
if (!function_exists('warningMsg')) {
	function warningMsg(string $message, int $duration = 2800): array
	{
		return messageGenerator(message: $message, icon: 'warning', duration: $duration, key: 'warning');
	}
}

/**
 * Génère un message d'erreur
 *
 * @param string $message Le message à afficher
 * @param int $duration La durée d'affichage du message
 * @return array
 * @package IAI-SITE
 * @author SOSSOU-GAH Ézéchiel
 * @created 2024-07-10
 */
if (!function_exists('errorMsg')) {
	function errorMsg(string $message, int $duration = 2800): array
	{
		return messageGenerator(message: $message, icon: 'warning', duration: $duration, key: 'danger');
	}
}

/**
 * Génère un message indiquant qu'un élément ne peut pas être supprimé
 *
 * @param string $itemName Le nom de l'élément
 * @return string[] Un tableau contenant le message généré
 * @package IAI-SITE
 * @created 2024-07-10
 */
if (!function_exists('cannotDeleteItemMessage')) {
	function cannotDeleteItemMessage(string $itemName): array
	{
		return warningMsg(Str::replace(':name', $itemName, config('messages.cannot.delete')));
	}
}

/**
 * Génère une alerte d'erreur
 *
 * @param string $message Le message à afficher
 * @param string $describedby L'ID de l'élément associé à l'alerte
 * @return string|null Le code HTML de l'alerte d'erreur
 * @package IAI-SITE
 * @created 2024-07-10
 */
if (!function_exists('errorAlert')) {
	function errorAlert(string $message, string $describedby = 'emailHelp'): string|null
	{
		return "<small id=" . $describedby . " class='form-text text-danger'>" . $message . "</small>";
	}
}
