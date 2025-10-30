<?php

use App\Models\Contact;

/**
 * Retourne le nombre de messages non lus dans la base de données
 *
 * @return int
 * @package IAI-SITE
 * @created 2024-07-10
 * @author SOSSOU-GAH Ézéchiel
 */
if (!function_exists('unreadMessagesCount')) {
	function unreadMessagesCount(): int
	{
		return Contact::query()->where('status', 0)->count();
	}
}
