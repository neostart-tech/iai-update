<?php

namespace App\Http\Controllers\EtudiantAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EtudiantLoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use MercurySeries\Flashy\Flashy;

class AuthenticatedSessionController extends Controller
{
	public function create(): View
	{
		return view('auth.etudiants.login');
	}

	/**
	 * @throws ValidationException
	 */
	public function store(EtudiantLoginRequest $request): RedirectResponse
	{

		
		$request->authenticate();

		$request->session()->regenerate();
		// Flashy::success($request->user('etudiants')->greeting(), icon: 'waving_hand');
		return redirect()->intended(RouteServiceProvider::ETUDIANT_HOME);
	}

	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('etudiants')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return to_route('home');
	}
}
