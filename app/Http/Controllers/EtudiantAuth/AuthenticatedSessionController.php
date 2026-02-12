<?php

namespace App\Http\Controllers\EtudiantAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EtudiantLoginRequest;
use App\Models\Etudiant;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
	public function store(EtudiantLoginRequest $request)
	{
		// //Code quaund on veut utliser le blade
		// $request->authenticate();

		// $request->session()->regenerate();
		// // Flashy::success($request->user('etudiants')->greeting(), icon: 'waving_hand');
		// return redirect()->intended(route('etudiants.auth.login'));

		//Code quaund on veut utliser le nuxt
		$request->validated();
		$credentials = $request->only('email', 'password');
		//Retouner une repsonse json vu que cette route sera comme uen api en nuxt 
		$user = Etudiant::where('email', $credentials['email'])->first();
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

	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('etudiants')->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return to_route('home');
	}
}
