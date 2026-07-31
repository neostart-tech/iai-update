<?php

namespace App\Support;

use Spatie\Activitylog\Models\Activity;

/**
 * Traduit une entrée du journal d'activité en une phrase claire et naturelle en français.
 */
class ActivityDescriber
{
	private const DOMAIN_LABELS = [
		'login' => 'Application',
		'me-connecter' => 'Application',
		'candidatures' => 'Candidatures',
		'etudiants' => 'Étudiants',
		'roles' => 'Rôles',
		'users' => 'Utilisateurs',
		'filieres' => 'Filières',
		'salles' => 'Salles',
		'ues' => 'Unités d\'enseignement',
		'uvs' => 'Unités de valeur',
		'evaluations' => 'Évaluations',
		'groups' => 'Groupes',
		'periodes' => 'Semestres',
		'edt' => 'Emplois du temps',
		'fiches' => 'Fiches de présence',
		'presences' => 'Présences',
		'events' => 'Évènements',
		'urgent_infos' => 'Actualités',
		'urgent_infos_public' => 'Actualités',
		'negociations' => 'Négociations',
		'paiements' => 'Paiements',
		'depenses' => 'Dépenses',
		'plan-de-paiement' => 'Plans de paiement',
		'tranche-de-paiement' => 'Tranches de paiement',
		'frais-inscription' => 'Tarifs de frais d\'inscription',
		'frais' => 'Frais de scolarité',
		'finance' => 'Opérations financières',
		'blogs' => 'Publications',
		'announcements' => 'Opportunités',
		'advertisers' => 'Partenaires',
		'gallery' => 'Galerie',
		'messages' => 'Messages',
		'prospects' => 'Prospects',
		'reclamations' => 'Réclamations',
		'notifications' => 'Notifications',
		'surveillants' => 'Surveillants',
		'niveau' => 'Niveaux',
		'document-types' => 'Types de document',
		'candidature-field-configs' => 'Champs de formulaire de candidature',
		'logs' => 'Journal d\'activité',
		'releves' => 'Relevés de notes',
		'releves-de-note' => 'Relevés de notes',
		'communications' => 'Communications',
		'jours-feries' => 'Jours fériés',
	];

	private const VERB_LABELS = [
		'login' => 'Connexion :',
		'me-connecter' => 'Connexion :',
		'logout' => 'Déconnexion :',
		'me-deconnecter' => 'Déconnexion :',
		'index' => 'Consultation :',
		'liste' => 'Consultation :',
		'show' => 'Consultation :',
		'detail' => 'Consultation :',
		'create' => 'Ouverture du formulaire :',
		'edit' => 'Ouverture du formulaire de modification :',
		'store-by-admin' => 'Création :',
		'update-by-admin' => 'Modification :',
		'store' => 'Création :',
		'add' => 'Ajout :',
		'ajouter' => 'Ajout :',
		'update' => 'Modification :',
		'modifier' => 'Modification :',
		'destroy' => 'Suppression :',
		'delete' => 'Suppression :',
		'supprimer' => 'Suppression :',
		'valider' => 'Validation :',
		'validate' => 'Validation :',
		'reject' => 'Rejet :',
		'rejeter' => 'Rejet :',
		'ask-for-rectification' => 'Demande de rectification :',
		'publish' => 'Publication :',
		'publier' => 'Publication :',
		'unpublish' => 'Dépublication :',
		'depublier' => 'Dépublication :',
		'duplicate' => 'Duplication :',
		'activate' => 'Activation :',
		'activer' => 'Activation :',
		'deactivate' => 'Désactivation :',
		'sync-permissions' => 'Modification des permissions :',
		'ajouter-paiement' => 'Ajout de paiement :',
		'export' => 'Exportation :',
		'download' => 'Téléchargement :',
		'telecharger' => 'Téléchargement :',
		'imprimer' => 'Impression :',
		'pdf' => 'Génération PDF :',
		'import' => 'Importation :',
		'reorienter' => 'Réorientation :',
		'inscrire-un-etudiant' => 'Inscription étudiant :',
		'send' => 'Envoi de message :',
		'rappel' => 'Envoi de rappel :',
		'abandon' => 'Déclaration d\'abandon :',
		'changer-mode-formation' => 'Changement du mode de formation :',
		'changer_mode_formation' => 'Changement du mode de formation :',
		'mode-formation' => 'Changement du mode de formation :',
	];

	private const METHOD_FALLBACK = [
		'GET' => 'Consultation :',
		'HEAD' => 'Consultation :',
		'POST' => 'Action :',
		'PUT' => 'Modification :',
		'PATCH' => 'Modification :',
		'DELETE' => 'Suppression :',
	];

	private const MODEL_LABELS = [
		'User' => 'utilisateur',
		'Role' => 'rôle',
		'Permission' => 'permission',
		'Note' => 'note',
		'Paiement' => 'paiement',
		'Candidature' => 'candidature',
	];

	private const EVENT_VERBS = [
		'created' => 'Création de',
		'updated' => 'Modification de',
		'deleted' => 'Suppression de',
		'restored' => 'Restauration de',
	];

	public static function describe(Activity $activity): string
	{
		if ($activity->log_name === 'http') {
			return self::describeHttp($activity);
		}

		return self::describeModelEvent($activity);
	}

	private static function describeHttp(Activity $activity): string
	{
		$route = $activity->properties['route'] ?? null;
		$method = $activity->properties['method'] ?? 'GET';
		$path = trim((string) ($activity->properties['path'] ?? ''), '/');
		$cleanPath = preg_replace('/^api\//i', '', $path);

		$rawSegments = $route ? explode('.', $route) : [];
		$segments = array_values(array_filter($rawSegments, fn($s) => !in_array($s, ['api', 'v1', 'admin'], true)));

		$domainKey = $segments[0] ?? (explode('/', $cleanPath)[0] ?? '');
		if ($domainKey === 'api' || $domainKey === '') {
			$pathParts = explode('/', $cleanPath);
			$domainKey = $pathParts[0] ?? 'l\'application';
		}

		$actionKey = end($rawSegments) ?: $cleanPath;

		$domainLabel = self::DOMAIN_LABELS[$domainKey] ?? self::humanizeFallback($domainKey);
		$subjectLabel = $activity->properties['subject_label'] ?? null;

		// Résolution dynamique depuis le payload si non présent
		if (!$subjectLabel && isset($activity->properties['payload']['etudiant_id'])) {
			$eId = (int) $activity->properties['payload']['etudiant_id'];
			$subjectLabel = self::resolveRealLabel('etudiants', $eId);
		}

		if (!$subjectLabel && isset($activity->properties['payload']['champs']) && is_array($activity->properties['payload']['champs'])) {
			$labels = collect($activity->properties['payload']['champs'])->pluck('label')->filter()->take(3)->implode(', ');
			if (filled($labels)) {
				$subjectLabel = $labels . (count($activity->properties['payload']['champs']) > 3 ? '...' : '');
			}
		}

		if (!$subjectLabel && isset($activity->properties['payload']['config_value']) && is_array($activity->properties['payload']['config_value'])) {
			$keys = array_keys($activity->properties['payload']['config_value']);
			$formattedKeys = array_map(fn($k) => str_replace(['_', '-'], ' ', $k), $keys);
			$subjectLabel = implode(', ', array_slice($formattedKeys, 0, 3)) . (count($keys) > 3 ? '...' : '');
		}

		if ($subjectLabel && str_starts_with($subjectLabel, '#') && is_numeric(substr($subjectLabel, 1))) {
			$realLabel = self::resolveRealLabel($domainKey, (int) substr($subjectLabel, 1));
			if ($realLabel) {
				$subjectLabel = $realLabel;
			}
		}

		foreach (self::VERB_LABELS as $needle => $verb) {
			if (str_contains($actionKey, $needle) || str_contains($path, $needle)) {
				return "{$verb} {$domainLabel}" . self::withLabel($subjectLabel);
			}
		}

		$verb = self::METHOD_FALLBACK[$method] ?? 'Action sur';

		return "{$verb} {$domainLabel}" . self::withLabel($subjectLabel);
	}

	private static function describeModelEvent(Activity $activity): string
	{
		$modelLabel = self::MODEL_LABELS[class_basename((string) $activity->subject_type)] ?? 'l\'élément';
		$verb = self::EVENT_VERBS[$activity->event] ?? (self::EVENT_VERBS[$activity->description] ?? 'Modification de');

		$subject = $activity->subject;
		$subjectLabel = $subject ? self::extractModelLabel($subject) : null;

		return "{$verb} {$modelLabel}" . self::withLabel($subjectLabel);
	}

	private static function withLabel(?string $label): string
	{
		if (!$label) {
			return '';
		}

		return " : \"{$label}\"";
	}

	private static function extractModelLabel(\Illuminate\Database\Eloquent\Model $model): ?string
	{
		if (filled($model->nom ?? null) && filled($model->prenom ?? null)) {
			return trim($model->nom . ' ' . $model->prenom);
		}

		foreach (['nom', 'libelle', 'titre', 'label', 'nom_affichage', 'email'] as $field) {
			if (filled($model->{$field} ?? null)) {
				return (string) $model->{$field};
			}
		}

		return null;
	}

	private static function humanizeFallback(string $key): string
	{
		if ($key === '' || $key === 'http' || $key === 'api') {
			return 'l\'application';
		}

		$dict = [
			'logs' => 'journal d\'activité',
			'candidature-field-configs' => 'champs de candidature',
			'users' => 'utilisateurs',
			'roles' => 'rôles',
			'candidatures' => 'candidatures',
			'etudiants' => 'étudiants',
		];

		if (isset($dict[$key])) {
			return $dict[$key];
		}

		return str_replace(['-', '_'], ' ', $key);
	}

	private static function resolveRealLabel(string $domainKey, int $id): ?string
	{
		$map = [
			'blogs' => \App\Models\Blog::class,
			'publications' => \App\Models\Blog::class,
			'candidatures' => \App\Models\Candidature::class,
			'candidature' => \App\Models\Candidature::class,
			'users' => \App\Models\User::class,
			'roles' => \Spatie\Permission\Models\Role::class,
			'etudiants' => \App\Models\Etudiant::class,
			'filieres' => \App\Models\Filiere::class,
			'salles' => \App\Models\Salle::class,
			'events' => \App\Models\Event::class,
			'evenements' => \App\Models\Event::class,
			'announcements' => \App\Models\Announcement::class,
			'opportunites' => \App\Models\Announcement::class,
			'urgent_infos' => \App\Models\UrgentInfo::class,
		];

		$modelClass = $map[$domainKey] ?? null;
		if (!$modelClass || !class_exists($modelClass)) {
			return null;
		}

		try {
			$model = $modelClass::find($id);
			if (!$model) {
				return null;
			}

			if (filled($model->nom ?? null) && filled($model->prenom ?? null)) {
				return trim($model->nom . ' ' . $model->prenom);
			}

			foreach (['title', 'titre', 'nom', 'libelle', 'label', 'nom_affichage', 'sujet', 'name', 'code', 'email'] as $field) {
				if (filled($model->{$field} ?? null)) {
					return (string) $model->{$field};
				}
			}
		} catch (\Throwable $e) {
			return null;
		}

		return null;
	}
}
