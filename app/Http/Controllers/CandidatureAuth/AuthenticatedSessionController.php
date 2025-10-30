<?php

namespace App\Http\Controllers\CandidatureAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CandidatureAuth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use MercurySeries\Flashy\Flashy;

class AuthenticatedSessionController extends Controller
{
	public function create(): View
	{
		return view('officiel.auth.login');
	}

	/**
	 * @throws ValidationException
	 */
	public function store(LoginRequest $request): RedirectResponse
	{
		$request->authenticate();

		$request->session()->regenerate();
		// Flashy::success($request->user('web_candidatures')->greeting(), icon: 'waving_hand');
		return redirect()->intended(RouteServiceProvider::CANDIDAT_HOME);
	}

	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('web_candidatures')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();
		return to_route('home');
	}
}
