<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Journalise chaque requête (lecture et mutation) : qui, quoi, quand, où.
 * Voir config/activitylog.php pour la config du package spatie/laravel-activitylog
 * et App\Http\Resources\ActivityLogResource pour ce qui est exposé côté admin.
 */
class LogActivity
{
	/**
	 * Champs jamais journalisés en clair, quel que soit l'endpoint.
	 */
	protected array $redactedKeys = [
		'password',
		'password_confirmation',
		'current_password',
		'new_password',
		'new_password_confirmation',
		'mot_de_passe',
		'token',
		'api_token',
		'access_token',
		'refresh_token',
		'secret',
	];

	/**
	 * Routes bruyantes/sans intérêt métier à ne pas journaliser (sondes de présence,
	 * notifications non lues, etc.) : loguer littéralement "toute" requête inclurait
	 * des appels de polling toutes les quelques secondes sans aucune valeur d'audit.
	 */
	protected array $excludedPathPatterns = [
		'sanctum/csrf-cookie',
		'notifications/unread',
		'api/notifications/unread',
		'_nuxt/*',
		'up',
		// Rafraîchissement automatique de l'utilisateur connecté (toutes les 60s,
		// voir gestion-ecole/app/plugins/permissions-refresh.client.ts) : aucune
		// valeur d'audit, et sans nom de route ⇒ s'affichait comme "a consulté"
		// tout court dans le journal.
		'api/user',
		// Configuration générale du site (nom, logo...), chargée en arrière-plan sur
		// à peu près toutes les pages, y compris publiques ⇒ apparaissait comme
		// "Système / anonyme a consulté ... (configuration)", ce qui n'a aucun sens
		// pour l'utilisateur du journal.
		'api/parametre/configuration',
	];

	public function handle(Request $request, Closure $next): Response
	{
		// Capturé en try/catch (et pas seulement après $next()) : une exception
		// (401/403/419/500...) traverse la pile de middlewares sans repasser par
		// le code qui suit $next() dans chacun d'eux — sans ça, tout refus d'accès
		// ou erreur serveur échapperait totalement au journal.
		try {
			$response = $next($request);
			$this->record($request, $response->getStatusCode());
			return $response;
		} catch (\Throwable $e) {
			$status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
			$this->record($request, $status, $e);
			throw $e;
		}
	}

	protected function shouldLog(Request $request): bool
	{
		foreach ($this->excludedPathPatterns as $pattern) {
			if ($request->is($pattern)) {
				return false;
			}
		}

		return true;
	}

	protected function record(Request $request, int $statusCode, ?\Throwable $exception = null): void
	{
		if (!$this->shouldLog($request)) {
			return;
		}

		$properties = [
			'method' => $request->method(),
			'path' => '/' . ltrim($request->path(), '/'),
			'route' => optional($request->route())->getName(),
			'status' => $statusCode,
			'ip' => $request->ip(),
			'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
			'payload' => $this->sanitize($request->except(array_merge($this->redactedKeys, ['_token']))),
		];

		// Nom/libellé lisible de l'élément concerné (ex: "Jean Dupont" pour
		// {user}/{candidature}...), quand la route a un paramètre résolu en modèle
		// Eloquent (binding implicite). Permet à ActivityDescriber d'écrire
		// "a modifié un utilisateur : Jean Dupont" plutôt que juste "un utilisateur".
		if ($subjectLabel = $this->extractSubjectLabel($request)) {
			$properties['subject_label'] = $subjectLabel;
		}

		if ($exception) {
			$properties['exception'] = get_class($exception);
		}

		activity('http')
			->causedBy($request->user())
			->withProperties($properties)
			->log($request->method() . ' ' . $request->path());
	}

	/**
	 * Cherche parmi les paramètres de route déjà résolus par Laravel (route model
	 * binding implicite, donc de vrais modèles Eloquent, pas de simples ID) celui
	 * qui correspond le mieux à "l'élément concerné" par la requête, et en tire un
	 * libellé humain (nom + prénom, nom, libellé...). Renvoie null si aucun
	 * paramètre de route n'est un modèle, ou si aucun champ usuel n'est trouvé.
	 */
	protected function extractSubjectLabel(Request $request): ?string
	{
		$route = $request->route();
		if (!$route) {
			return null;
		}

		foreach ($route->parameters() as $value) {
			if (!$value instanceof \Illuminate\Database\Eloquent\Model) {
				continue;
			}

			if (filled($value->nom ?? null) && filled($value->prenom ?? null)) {
				return trim($value->nom . ' ' . $value->prenom);
			}

			foreach (['nom', 'libelle', 'titre', 'label', 'nom_affichage', 'email'] as $field) {
				if (filled($value->{$field} ?? null)) {
					return (string) $value->{$field};
				}
			}

			return '#' . $value->getKey();
		}

		return null;
	}

	protected function sanitize(array $data): array
	{
		array_walk_recursive($data, function (&$value, $key) {
			if (is_string($key) && in_array(mb_strtolower($key), $this->redactedKeys, true)) {
				$value = '••••••••';
			}
		});

		return $data;
	}
}
