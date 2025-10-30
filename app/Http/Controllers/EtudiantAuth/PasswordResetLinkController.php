<?php

namespace App\Http\Controllers\EtudiantAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
	public function create(): View
	{
		return view('auth.etudiants.forgot-password');
	}

	public function store(Request $request): RedirectResponse
	{
		$request->validate([
			'email' => ['required', 'email'],
		], [
			'email.required' => 'Votre adresse email est obligatoire',
			'email.email' => 'Votre adresse email n\'est pas une adresse mail valide'
		]);

		Password::setDefaultDriver('etudiants');
		$status = Password::sendResetLink($request->only('email'));

		return $status == Password::RESET_LINK_SENT
			? back()->with('status', __($status))
			: back()->withInput($request->only('email'))
				->withErrors(['email' => __($status)]);
	}
}
