<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use MercurySeries\Flashy\Flashy;

class AuthenticatedSessionController extends Controller
{
	public function create(): View
	{
		return view('auth.login');
	}

	/**
	 * @throws ValidationException
	 */
	public function store(LoginRequest $request): RedirectResponse
	{
		$request->authenticate();

		$request->session()->regenerate();
		// Flashy::success($request->user()->greeting(), icon: 'waving_hand');
		return redirect()->intended(route('mon-dashboard'));
	}

	/**
	 * Destroy an authenticated session.
	 */
	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('web')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return redirect('/');
	}
}
