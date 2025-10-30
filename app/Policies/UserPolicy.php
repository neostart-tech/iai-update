<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
	public function viewAny(User $user): bool
	{
		return true;
	}

	public function view(User $user, User $model): bool
	{
		return true;
	}

	public function create(?User $user): bool
	{
		return $user->hasRoles(13, 14);
	}

	public function update(User $user, User $model): bool
	{
		return $user->hasRoles(13, 14) or $user->getAttribute('id') === $model->getAttribute('id');
	}

	public function delete(?User $user, User $model): bool
	{
		return $user->hasRoles(13, 14);
	}

	public function restore(User $user, User $model): bool
	{
		return false;
	}

	public function forceDelete(User $user, User $model): bool
	{
		return false;
	}
}
