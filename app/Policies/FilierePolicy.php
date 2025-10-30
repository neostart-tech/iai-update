<?php

namespace App\Policies;

use App\Models\{Filiere, User};
use Illuminate\Auth\Access\Response;

class FilierePolicy
{
	public function viewAny(User $user): bool
	{
		return true;
	}

	public function view(User $user, Filiere $filiere): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $user): bool
	{
		return $user->hasRoles(14, 13, 6);
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(User $user, Filiere $filiere): bool
	{
		return $user->hasRoles(13, 6);
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(User $user, Filiere $filiere): bool
	{
		return $user->hasRoles(13, 6);
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(User $user, Filiere $filiere): bool
	{
		return $user->hasRoles(13, 6);
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(User $user, Filiere $filiere): bool
	{
		return $user->hasRoles(13, 6);
	}
}
