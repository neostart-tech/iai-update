<?php

namespace App\Support;

use Spatie\Activitylog\Models\Activity;

/**
 * Traduit une entrée du journal d'activité (technique : méthode/route, ou
 * événement de modèle Eloquent) en une phrase en français compréhensible par
 * un utilisateur sans connaissance informatique. Les colonnes techniques
 * (méthode/chemin/statut/module) restent affichées à côté, cette phrase
 * s'ajoute, elle ne les remplace pas.
 */
class ActivityDescriber
{
	/**
	 * Préfixe de route (premier segment du nom de route) => libellé au singulier,
	 * avec article, tel qu'utilisé après un verbe ("a créé {libellé}").
	 */
	private const DOMAIN_LABELS = [
		'candidatures' => 'une candidature',
		'etudiants' => 'un étudiant',
		'roles' => 'un rôle',
		'users' => 'un utilisateur',
		'filieres' => 'une filière',
		'salles' => 'une salle',
		'ues' => 'une unité d\'enseignement',
		'uvs' => 'une unité de valeur',
		'evaluations' => 'une évaluation',
		'groups' => 'un groupe',
		'periodes' => 'un semestre',
		'edt' => 'un cours',
		'fiches' => 'une fiche de présence',
		'presences' => 'une présence',
		'events' => 'un évènement',
		'urgent_infos' => 'une actualité',
		'urgent_infos_public' => 'une actualité',
		'negociations' => 'une négociation',
		'paiements' => 'un paiement',
		'depenses' => 'une dépense',
		'plan-de-paiement' => 'un plan de paiement',
		'tranche-de-paiement' => 'une tranche de paiement',
		'frais-inscription' => 'un tarif de frais d\'inscription',
		'frais' => 'un frais de scolarité',
		'finance' => 'une opération financière',
		'blogs' => 'une publication',
		'announcements' => 'une opportunité',
		'advertisers' => 'un partenaire',
		'gallery' => 'un élément de galerie',
		'messages' => 'un message',
		'prospects' => 'un prospect',
		'reclamations' => 'une réclamation',
		'notifications' => 'une notification',
		'surveillants' => 'un surveillant',
		'niveau' => 'un niveau',
		'document-types' => 'un type de document',
		'candidature-field-configs' => 'un champ de formulaire de candidature',
		'releves' => 'un relevé de notes',
		'releves-de-note' => 'un relevé de notes',
		'communications' => 'une communication',
		'jours-feries' => 'un jour férié',
	];

	/**
	 * Mot-clé (préfixe) détecté dans le dernier segment du nom de route => verbe.
	 * Vérifié dans l'ordre : le premier préfixe qui matche gagne, donc les
	 * entrées plus spécifiques doivent être placées avant les plus génériques.
	 */
	private const VERB_LABELS = [
		'index' => 'a consulté la liste concernant',
		'liste' => 'a consulté la liste concernant',
		'show' => 'a consulté le détail de',
		'detail' => 'a consulté le détail de',
		'create' => 'a ouvert le formulaire de création de',
		'edit' => 'a ouvert le formulaire de modification de',
		'store-by-admin' => 'a créé',
		'update-by-admin' => 'a modifié',
		'store' => 'a créé',
		'add' => 'a ajouté',
		'ajouter' => 'a ajouté',
		'update' => 'a modifié',
		'modifier' => 'a modifié',
		'destroy' => 'a supprimé',
		'delete' => 'a supprimé',
		'supprimer' => 'a supprimé',
		'valider' => 'a validé',
		'validate' => 'a validé',
		'reject' => 'a rejeté',
		'rejeter' => 'a rejeté',
		'ask-for-rectification' => 'a demandé une rectification pour',
		'publish' => 'a publié',
		'publier' => 'a publié',
		'unpublish' => 'a dépublié',
		'depublier' => 'a dépublié',
		'duplicate' => 'a dupliqué',
		'activate' => 'a activé',
		'activer' => 'a activé',
		'deactivate' => 'a désactivé',
		'sync-permissions' => 'a modifié les permissions de',
		'ajouter-paiement' => 'a ajouté un paiement pour',
		'export' => 'a exporté',
		'import' => 'a importé',
		'reorienter' => 'a réorienté',
		'inscrire-un-etudiant' => 'a inscrit un étudiant depuis',
		'send' => 'a envoyé un message concernant',
		'rappel' => 'a envoyé un rappel concernant',
		'abandon' => 'a déclaré un abandon pour',
	];

	private const METHOD_FALLBACK = [
		'GET' => 'a consulté',
		'HEAD' => 'a consulté',
		'POST' => 'a effectué une action sur',
		'PUT' => 'a modifié',
		'PATCH' => 'a modifié',
		'DELETE' => 'a supprimé',
	];

	private const MODEL_LABELS = [
		'User' => 'un utilisateur',
		'Role' => 'un rôle',
		'Permission' => 'une permission',
		'Note' => 'une note',
		'Paiement' => 'un paiement',
		'Candidature' => 'une candidature',
	];

	private const EVENT_VERBS = [
		'created' => 'a créé',
		'updated' => 'a modifié',
		'deleted' => 'a supprimé',
		'restored' => 'a restauré',
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

		// Route sans nom (closure) : impossible de déduire quoi que ce soit du nom
		// de route, mais on ne veut jamais afficher un "a consulté" nu — on retombe
		// sur le chemin de l'URL, humanisé de la même façon qu'un domaine inconnu.
		if (!$route) {
			$verb = self::METHOD_FALLBACK[$method] ?? 'a effectué une action sur';
			$path = trim((string) ($activity->properties['path'] ?? ''), '/');
			$lastSegment = $path !== '' ? collect(explode('/', $path))->last() : '';

			return "{$verb} " . self::humanizeFallback($lastSegment);
		}

		$segments = explode('.', $route);
		$domainKey = $segments[0] ?? '';
		$actionKey = end($segments);

		$domainLabel = self::DOMAIN_LABELS[$domainKey] ?? self::humanizeFallback($domainKey);
		$subjectLabel = $activity->properties['subject_label'] ?? null;

		foreach (self::VERB_LABELS as $needle => $verb) {
			if (str_contains($actionKey, $needle)) {
				return "{$verb} {$domainLabel}" . self::withLabel($subjectLabel);
			}
		}

		$verb = self::METHOD_FALLBACK[$method] ?? 'a effectué une action sur';

		return "{$verb} {$domainLabel}" . self::withLabel($subjectLabel);
	}

	private static function describeModelEvent(Activity $activity): string
	{
		$modelLabel = self::MODEL_LABELS[class_basename((string) $activity->subject_type)] ?? 'un élément';
		$verb = self::EVENT_VERBS[$activity->event] ?? (self::EVENT_VERBS[$activity->description] ?? 'a modifié');

		// $activity->subject peut être null si l'élément a depuis été supprimé
		// (ex: on consulte le journal après qu'un utilisateur a été effacé).
		$subject = $activity->subject;
		$subjectLabel = $subject ? self::extractModelLabel($subject) : null;

		return "{$verb} {$modelLabel}" . self::withLabel($subjectLabel);
	}

	/**
	 * Formatte le nom précis de l'élément concerné, quand on l'a — pour que
	 * l'utilisateur sache EXACTEMENT qui/quoi a été touché, pas juste la
	 * catégorie ("a modifié un utilisateur : Jean Dupont", pas juste
	 * "a modifié un utilisateur").
	 */
	private static function withLabel(?string $label): string
	{
		return $label ? " : {$label}" : '';
	}

	/**
	 * Même logique de priorité de champs que LogActivity::extractSubjectLabel(),
	 * mais appliquée directement au modèle Eloquent (disponible sans détour par
	 * la requête HTTP pour les logs d'évènement de modèle).
	 */
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
		if ($key === '') {
			return 'le système';
		}

		return 'un élément (' . str_replace(['-', '_'], ' ', $key) . ')';
	}
}
