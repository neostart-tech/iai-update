<?php

namespace App\Traits\ActionsTraits;

use Symfony\Component\HttpKernel\Exception\HttpException;

trait AuthorizesConcoursManagementTrait
{
	/**
	 * Rôles autorisés à consulter/gérer les sessions de concours, matières,
	 * coefficients et notes — doit rester aligné avec la liste utilisée côté
	 * frontend pour l'affichage du menu (SidebarApp.vue).
	 */
	private const ROLES_AUTORISES = [
		'directeur-academique',
		'directeur-general-adjoint',
		'directeur-general',
		'admin',
		'informaticien',
		'charge-de-la-clientele',
		'logiticien-academique',
	];

	private function authorizeConcoursManagement(): void
	{
		$user = auth('sanctum')->user() ?? auth()->user();

		if (!$user) {
			throw new HttpException(401, 'Authentification requise.');
		}

		$rolesUtilisateur = method_exists($user, 'roles')
			? $user->roles->pluck('slug')->all()
			: [];

		if (!array_intersect(self::ROLES_AUTORISES, $rolesUtilisateur)) {
			throw new HttpException(403, "Vous n'avez pas la permission de gérer les sessions de concours.");
		}
	}
}
