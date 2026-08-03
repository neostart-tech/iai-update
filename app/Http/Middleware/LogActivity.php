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
		// Actions intermédiaires lors des examens (sauvegarde automatique, réponse question par question, progression) :
		// Ne pas engorger le journal d'activité ; seule la soumission finale (submit-all) est journalisée.
		'api/exam/*/save',
		'api/exam/*/submit-question',
		'api/exam/*/progress',
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
		// Ne pas journaliser les requêtes si l'utilisateur n'est pas connecté dans le système
		// (ex: visiteur externe, consultation du site public sans compte).
		if (!$request->user()) {
			return false;
		}

		foreach ($this->excludedPathPatterns as $pattern) {
			if ($request->is($pattern)) {
				return false;
			}
		}

		// Ne pas journaliser les requêtes de simple lecture (GET/HEAD) :
		// Le journal d'activité a pour vocation de tracer les requêtes ayant un impact métier
		// (Création, Modification, Suppression, Connexion, Exportation/Téléchargement).
		// Seuls les exports, téléchargements de documents ou impressions sont conservés en GET.
		if (in_array($request->method(), ['GET', 'HEAD'], true)) {
			$path = mb_strtolower($request->path());
			$isExportOrDownload = str_contains($path, 'export')
				|| str_contains($path, 'download')
				|| str_contains($path, 'telecharger')
				|| str_contains($path, 'imprimer')
				|| str_contains($path, 'print')
				|| str_contains($path, 'pdf')
				|| str_contains($path, 'releve')
				|| str_contains($path, 'attestation')
				|| str_contains($path, 'facture');
			if (!$isExportOrDownload) {
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
	 * Cherche la désignation lisible de l'élément concerné (titre de la publication,
	 * nom de l'utilisateur/candidat, libellé de la filière, etc.).
	 * 1. Depuis la charge utile de la requête ($request->input('titre'), nom, libelle...)
	 * 2. Depuis les paramètres résolus de la route (modèles Eloquent)
	 * 3. Depuis la base de données si l'ID est transmis en paramètre de route
	 */
	protected function extractSubjectLabel(Request $request): ?string
	{
		// 0. Si un etudiant_id / user_id est passé dans le payload
		if ($etudiantId = $request->input('etudiant_id')) {
			if (is_numeric($etudiantId) && $etudiant = \App\Models\Etudiant::find($etudiantId)) {
				return $this->extractLabelFromModel($etudiant);
			}
		}

		if ($userId = $request->input('user_id')) {
			if (is_numeric($userId) && $user = \App\Models\User::find($userId)) {
				return $this->extractLabelFromModel($user);
			}
		}

		if ($candidatureId = $request->input('candidature_id')) {
			if (is_numeric($candidatureId) && $candidature = \App\Models\Candidature::find($candidatureId)) {
				return $this->extractLabelFromModel($candidature);
			}
		}

		if ($etudiantIds = $request->input('etudiant_ids')) {
			if (is_array($etudiantIds) && count($etudiantIds) > 0) {
				return count($etudiantIds) . ' étudiant(s)';
			}
		}

		// Extraction spécifique pour les configurations système et champs de candidature
		if ($request->has('champs') && is_array($request->input('champs'))) {
			$labels = collect($request->input('champs'))->pluck('label')->filter()->take(3)->implode(', ');
			if (filled($labels)) {
				return $labels . (count($request->input('champs')) > 3 ? '...' : '');
			}
		}

		if ($request->has('config_value') && is_array($request->input('config_value'))) {
			$keys = array_keys($request->input('config_value'));
			$formattedKeys = array_map(fn($k) => str_replace(['_', '-'], ' ', $k), $keys);
			return implode(', ', array_slice($formattedKeys, 0, 3)) . (count($keys) > 3 ? '...' : '');
		}

		if ($key = $request->input('key')) {
			if (is_string($key) && filled($key)) {
				return str_replace(['_', '-'], ' ', $key);
			}
		}

		// 1. Extraire depuis le payload s'il s'agit d'un formulaire (ex: titre de blog, nom d'utilisateur...)
		$payloadNom = $request->input('nom');
		$payloadPrenom = $request->input('prenom');

		if (filled($payloadNom) && filled($payloadPrenom)) {
			return trim($payloadNom . ' ' . $payloadPrenom);
		}

		foreach (['titre', 'title', 'nom', 'libelle', 'label', 'nom_affichage', 'sujet', 'name'] as $field) {
			$val = $request->input($field);
			if (filled($val) && is_string($val)) {
				return mb_substr(trim($val), 0, 100);
			}
		}

		$route = $request->route();
		if (!$route) {
			return null;
		}

		// 2. Parcourir les paramètres de route
		foreach ($route->parameters() as $key => $value) {
			if ($value instanceof \Illuminate\Database\Eloquent\Model) {
				return $this->extractLabelFromModel($value);
			}

			if (is_scalar($value) && filled($value)) {
				$model = $this->findModelForRoute($route->getName(), (string) $key, $value);
				if ($model) {
					return $this->extractLabelFromModel($model);
				}
			}
		}

		return null;
	}

	protected function extractLabelFromModel(\Illuminate\Database\Eloquent\Model $model): ?string
	{
		if (filled($model->nom ?? null) && filled($model->prenom ?? null)) {
			return trim($model->nom . ' ' . $model->prenom);
		}

		foreach (['titre', 'title', 'nom', 'libelle', 'label', 'nom_affichage', 'sujet', 'name', 'code', 'email'] as $field) {
			if (filled($model->{$field} ?? null)) {
				return (string) $model->{$field};
			}
		}

		return null;
	}

	protected function findModelForRoute(?string $routeName, string $paramKey, mixed $id): ?\Illuminate\Database\Eloquent\Model
	{
		if (!$routeName || !is_numeric($id)) {
			return null;
		}

		$map = [
			'blogs' => \App\Models\Blog::class,
			'candidatures' => \App\Models\Candidature::class,
			'users' => \App\Models\User::class,
			'roles' => \Spatie\Permission\Models\Role::class,
			'etudiants' => \App\Models\Etudiant::class,
			'filieres' => \App\Models\Filiere::class,
			'salles' => \App\Models\Salle::class,
			'events' => \App\Models\Event::class,
			'announcements' => \App\Models\Announcement::class,
			'urgent_infos' => \App\Models\UrgentInfo::class,
		];

		$domain = explode('.', $routeName)[0] ?? '';
		$modelClass = $map[$domain] ?? null;

		if ($modelClass && class_exists($modelClass)) {
			try {
				return $modelClass::find($id);
			} catch (\Throwable $e) {
				return null;
			}
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
