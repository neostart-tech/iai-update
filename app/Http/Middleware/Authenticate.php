<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Authenticate extends Middleware
{
	/**
	 * Get the path the user should be redirected to when they are not authenticated.
	 */
	protected function redirectTo(Request $request): ?string
	{
		if ($request->expectsJson()) return null;

		if (Str::contains($request->route()->uri(), 'espace-etudiant'))
			return route('etudiants.auth.login');
		elseif (Str::contains($request->route()->uri(), 'espace-candidat'))
			return route('officiel.login');
		elseif (Str::contains($request->route()->uri(), 'administration'))
			return route('login');
		elseif (Str::contains($request->route()->uri(), 'espace-enseignant'))
			return route('enseignant.auth.login');
		elseif (Str::contains($request->route()->uri(), 'espace-comptable'))
			return route('comptable.auth.logincompta');
		else
			return route('home');
	}
}
