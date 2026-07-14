<?php

namespace App\Http\Controllers\CandidatureAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CandidatureAuth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
	/**
	 * Gère la tentative de connexion depuis l'API (Nuxt)
	 *
	 * @throws ValidationException
	 */
	public function store(LoginRequest $request): JsonResponse
	{
		// Authentifie l'utilisateur via la logique de la requête (vérifie les identifiants)
		$request->authenticate();

		// Récupère l'utilisateur
		$user = $request->user() ?? $request->user('web_candidatures');
		
		// Génère un jeton d'accès Sanctum
		$token = $user->createToken('candidat_token')->plainTextToken;

		return response()->json([
			'message' => 'Connecté avec succès',
			'user' => $user,
			'token' => $token
		]);
	}

	/**
	 * Déconnecte l'utilisateur en révoquant son jeton
	 */
	public function destroy(Request $request): JsonResponse
	{
		$user = auth('sanctum')->user() ?? $request->user();
		
		if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
			$user->currentAccessToken()->delete();
		}
		
		return response()->json(['message' => 'Déconnecté avec succès']);
	}
}
