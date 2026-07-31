<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
	public function index(Request $request)
	{
		$query = Activity::query()->with('causer')->latest('id');

		// Masquer les actions anonymes / utilisateurs externes non enregistrés
		$query->whereNotNull('causer_id');

		// Masquer le bruit des requêtes GET de simple consultation d'arrière-plan
		$query->where(function ($q) {
			$q->whereNull('properties->method')
				->orWhere('properties->method', '!=', 'GET')
				->orWhere('properties->path', 'like', '%export%')
				->orWhere('properties->path', 'like', '%download%')
				->orWhere('properties->path', 'like', '%telecharger%')
				->orWhere('properties->path', 'like', '%pdf%')
				->orWhere('properties->path', 'like', '%releve%')
				->orWhere('properties->path', 'like', '%attestation%');
		});

		if ($request->filled('user_id')) {
			$query->where('causer_id', $request->input('user_id'));
		}

		if ($request->filled('log_name')) {
			$query->where('log_name', $request->input('log_name'));
		}

		if ($request->filled('method')) {
			$query->where('properties->method', $request->input('method'));
		}

		if ($request->filled('date_from')) {
			$query->whereDate('created_at', '>=', $request->input('date_from'));
		}

		if ($request->filled('date_to')) {
			$query->whereDate('created_at', '<=', $request->input('date_to'));
		}

		if ($request->filled('search')) {
			$search = $request->input('search');
			$query->where(function ($q) use ($search) {
				$q->where('description', 'like', "%{$search}%")
					->orWhere('properties->path', 'like', "%{$search}%");
			});
		}

		$logs = $query->paginate($request->integer('per_page', 25));

		return ActivityLogResource::collection($logs);
	}

	/**
	 * Activité de l'utilisateur connecté uniquement (self-service, pas de
	 * permission dédiée : chacun peut consulter son propre historique — voir
	 * le module Paramètre). `causer_id` est forcé sur l'utilisateur authentifié,
	 * jamais pris depuis la requête, pour ne jamais exposer l'activité d'un tiers.
	 */
	public function mine(Request $request)
	{
		$query = Activity::query()
			->with('causer')
			->where('causer_type', \App\Models\User::class)
			->where('causer_id', $request->user()->id)
			->latest('id');

		$query->where(function ($q) {
			$q->whereNull('properties->method')
				->orWhere('properties->method', '!=', 'GET')
				->orWhere('properties->path', 'like', '%export%')
				->orWhere('properties->path', 'like', '%download%')
				->orWhere('properties->path', 'like', '%telecharger%')
				->orWhere('properties->path', 'like', '%pdf%')
				->orWhere('properties->path', 'like', '%releve%')
				->orWhere('properties->path', 'like', '%attestation%');
		});

		if ($request->filled('date_from')) {
			$query->whereDate('created_at', '>=', $request->input('date_from'));
		}

		if ($request->filled('date_to')) {
			$query->whereDate('created_at', '<=', $request->input('date_to'));
		}

		if ($request->filled('search')) {
			$search = $request->input('search');
			$query->where(function ($q) use ($search) {
				$q->where('description', 'like', "%{$search}%")
					->orWhere('properties->path', 'like', "%{$search}%");
			});
		}

		$logs = $query->paginate($request->integer('per_page', 25));

		return ActivityLogResource::collection($logs);
	}

	/**
	 * Modules distincts (log_name) déjà présents, pour peupler le filtre côté front
	 * sans coder en dur la liste (les noms de modèles suivent class_basename()).
	 */
	public function modules()
	{
		return response()->json([
			'data' => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name'),
		]);
	}

	public function destroy($id)
	{
		$user = auth()->user();
		if (!$user->can('delete-log') && !in_array($user->role?->slug ?? '', ['informaticien', 'directeur-general', 'directeur-general-adjoint', 'super-admin'])) {
			abort(403, 'Action non autorisée');
		}

		$log = Activity::findOrFail($id);
		$log->delete();

		return response()->json(['message' => 'Log supprimé avec succès']);
	}

	public function bulkDestroy(Request $request)
	{
		$user = auth()->user();
		if (!$user->can('delete-log') && !in_array($user->role?->slug ?? '', ['informaticien', 'directeur-general', 'directeur-general-adjoint', 'super-admin'])) {
			abort(403, 'Action non autorisée');
		}

		$request->validate([
			'ids' => 'required|array',
			'ids.*' => 'integer',
		]);

		Activity::whereIn('id', $request->input('ids'))->delete();

		return response()->json(['message' => 'Les logs sélectionnés ont été supprimés avec succès']);
	}
}
