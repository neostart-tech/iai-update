<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\Rules\Password;
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
	public function store(LoginRequest $request)
	{
		// $request->authenticate();

		// $request->session()->regenerate();
		// // Flashy::success($request->user()->greeting(), icon: 'waving_hand');
		// // return redirect()->intended(route('mon-dashboard'));
		// 		return redirect()->intended(route('my-calendar'));

		$request->validated();
		$credentials = $request->only('email', 'password');
		//Retouner une repsonse json vu que cette route sera comme uen api en nuxt 
		$user = User::where('email', $credentials['email'])->first();
		if ($user) {
			if (!Hash::check($credentials['password'], $user->password)) {
				return response()->json([
					'message' => 'Les informations de connexion sont invalides.'
				], 422);
			}

			Auth::login($user);

			$token = $user->createToken('auth_token')->plainTextToken;
		} else {
			return response()->json([
				'message' => 'Les informations de connexion sont invalides.'
			], 422);
		}

		return response()->json([
			'message' => 'Connexion réussie.',
			'user' => $user->load('roles'),
			'token' => $token,
		], 200);
	}

	/**
	 * Destroy an authenticated session.
	 */
	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('web')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return redirect()->route('login');
	}

}
