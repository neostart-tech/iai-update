<?php

namespace App\Http\Controllers\enseignantAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ComptableLoginRequest;
use App\Http\Requests\Auth\EnseignantLoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use MercurySeries\Flashy\Flashy;

class AuthentificationSessionController extends Controller
{
	public function login(): View
	{
		return view('auth.enseignants.login');
	}

	/**
	 * @throws ValidationException
	 */
	public function store(EnseignantLoginRequest $request): RedirectResponse
	{

		$request->authenticate();

		$request->session()->regenerate();
		// Flashy::success($request->user('etudiants')->greeting(), icon: 'waving_hand');
		return redirect()->intended(RouteServiceProvider::ENSEIGNANT_HOME);
	}

	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('enseignants')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return to_route('home');
	}


	/********/
		public function logincompta(): View
	{
		return view('auth.comptable.login');
	}

	/**
	 * @throws ValidationException
	 */
	public function storecompta(ComptableLoginRequest $request): RedirectResponse
	{

		$request->authenticate();

		$request->session()->regenerate();
		// Flashy::success($request->user('etudiants')->greeting(), icon: 'waving_hand');
		return redirect()->intended(RouteServiceProvider::COMPTABLE_HOME);
	}

	public function destroycompta(Request $request): RedirectResponse
	{
		Auth::guard('comptables')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return to_route('home');
	}
	
}
