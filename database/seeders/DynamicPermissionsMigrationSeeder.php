<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DynamicPermissionsMigrationSeeder extends Seeder
{
	/**
	 * Bascule du système de permissions statique (gestion-ecole/app/utils/roleAccess.js, codé en dur
	 * côté front) vers le système dynamique piloté par la base (permissions.slug + permission_role).
	 * Slugifie les permissions déjà seedées par domaine, crée celles qui manquaient pour des
	 * fonctionnalités déjà gérées côté front (UE, UV, évaluations, étudiants, groupes, semestres,
	 * relevés, actualités), puis réattribue aux rôles exactement l'accès qu'ils avaient dans
	 * l'ancien roleAccess.js pour ne rien casser pendant la bascule. Le rôle "admin" du fichier
	 * legacy n'a pas de contrepartie dans la table roles : ignoré.
	 */
	public function run(): void
	{
		$this->slugifyExistingPermissions();
		$this->createMissingPermissions();
		$this->syncRolePermissions();
	}

	private function slugifyExistingPermissions(): void
	{
		$bySlug = [
			// Filière
			'Ajouter une filière' => 'create-filiere',
			'Voir les filières' => 'view-filiere',
			'Modifier une filière' => 'update-filiere',
			'Supprimer une filière' => 'delete-filiere',

			// Salle
			'Ajouter une salle' => 'create-salle',
			'Voir les salles' => 'view-salle',
			'Modifier une salle' => 'update-salle',
			'Supprimer une salle' => 'delete-salle',

			// Enseignant (personnel)
			'Ajouter un membre du personnel' => 'create-enseignant',
			'Voir les membres du personnel' => 'view-enseignant',
			'Modifier un membre du personnel' => 'update-enseignant',
			'Supprimer un membre du personnel' => 'delete-enseignant',

			// Emploi du temps : c'est la ressource que le front appelle "cours"
			'Ajouter un emploi du temps' => 'create-cours',
			'Voir les emploi du temp' => 'view-cours',
			'Voir les emploi du temp de sa salle' => 'view-cours-salle',
			'Modifier un emploi du temps' => 'update-cours',
			'Supprimer un emploi du temps' => 'delete-cours',

			// Fiches de présence
			'Ajouter une fiche de présence' => 'create-fiche-presence',
			'Voir les fiches de présence' => 'view-fiche-presence',
			'Voir les fiches de présence de sa salle' => 'view-fiche-presence-salle',
			'Modifier une fiche de présence' => 'update-fiche-presence',
			'Supprimer une fiche de présence' => 'delete-fiche-presence',

			// Notes
			'Ajouter une note à un étudiant' => 'create-note',
			"Voir les notes d'un étudiant" => 'view-note',
			"Voir les notes d'un étudiant de sa salle" => 'view-note-salle',
			'Modifier une note à un étudiant' => 'update-note',
			"Modifier la note d'un étudiant après un certain temps" => 'update-note-apres-delai',
			'Supprimer une note à un étudiant' => 'delete-note',

			// Évènements (module "Active" dans les seeders existants)
			'Ajouter un évènement' => 'create-evenement',
			'Voir les évènements' => 'view-evenement',
			'Voir les évènement de sa salle' => 'view-evenement-salle',
			'Modifier un évènement' => 'update-evenement',
			'Supprimer un évènement' => 'delete-evenement',

			// Site vitrine : aucun contrôleur identifié à ce jour, slug posé pour cohérence, non câblé
			'Ajouter un élément au site publique' => 'create-site-vitrine',
			'Voir les éléments du site publique' => 'view-site-vitrine',
			'Modifier un élément au site publique' => 'update-site-vitrine',
			'Supprimer un élément au site publique' => 'delete-site-vitrine',

			// Candidatures (module "CandidatValidation" dans les seeders : en réalité du CRUD sur le dossier)
			'Ajouter un candidat entrant' => 'create-candidat-entrant',
			'Voir la liste des candidats' => 'view-candidat-entrant',
			'Modifier un candidat entrant' => 'update-candidat-entrant',
			'Supprimer un candidat entrant' => 'delete-candidat-entrant',

			// Frais de scolarité (pas de permission update/delete seedée à l'origine)
			'Ajouter un enregistrement de payement de frais de scolarité' => 'create-frais-scolarite',
			'Voir les enregistrements de payement de frais de scolarité' => 'view-frais-scolarite',
			'Voir ses enregistrements de payement de frais de scolarité' => 'view-frais-scolarite-personnel',

			// Divers
			'Voir la liste des payements de ses enfants' => 'view-payements-enfants',
			"Modifier le profil d'un autre utilisateur" => 'update-profil-autre-utilisateur',

			// Utilisateurs (module oublié lors de la bascule initiale : ces lignes
			// existaient déjà en base sans jamais avoir été sluggées, ce qui a cassé
			// silencieusement can:create-user/update-user/delete-user pour tout le
			// monde, y compris les rôles à accès complet -- détecté via permissions:audit).
			'Ajouter un utilisateur' => 'create-user',
			'Voir tous les utilisateurs' => 'view-user',
			'Modifier un utilisateur' => 'update-user',
			'Supprimer un utilisateur' => 'delete-user',
		];

		foreach ($bySlug as $nom => $slug) {
			// Idempotent : si le slug existe déjà (premier run, ou ré-exécution), on ne retente
			// pas — sinon le doublon "Voir les emploi du temp de sa salle" (EmploiDuTemp +
			// CandidatValidation, bug de seed historique) fait échouer la contrainte unique.
			if (Permission::where('slug', $slug)->exists()) {
				continue;
			}

			Permission::where('nom', $nom)
				->whereNull('slug')
				->orderBy('id')
				->limit(1)
				->update(['slug' => $slug]);
		}
	}

	private function createMissingPermissions(): void
	{
		$modules = [
			'ue' => "une unité d'enseignement",
			'uv' => 'une unité de valeur',
			'evaluation' => 'une évaluation',
			'etudiant' => 'un étudiant',
			'groupe' => 'un groupe',
			'semestre' => 'un semestre',
			'actualite' => 'une actualité',
		];

		$verbes = ['create' => 'Ajouter', 'view' => 'Voir', 'update' => 'Modifier', 'delete' => 'Supprimer'];

		foreach ($modules as $slugPrefix => $label) {
			foreach ($verbes as $action => $verbe) {
				$slug = "{$action}-{$slugPrefix}";
				Permission::firstOrCreate(
					['slug' => $slug],
					['nom' => "{$verbe} {$label}", 'description' => "{$verbe} {$label}"]
				);
			}
		}

		// "delete-etudiant" est un intitulé trompeur hérité de la boucle générique
		// ci-dessus : Admin\EtudiantController::destroy() ne supprime rien, il
		// bascule le statut actif/inactif (confirmé en lisant le contrôleur — le
		// bouton front s'appelle d'ailleurs "Désactiver/Réactiver l'étudiant", pas
		// "Supprimer"). Cet écart de libellé (l'action existe bien et reste câblée
		// sur cette permission, seul le nom affiché dans l'UI Rôles était faux) a
		// été signalé par l'utilisateur — corrigé ici pour que le nom reste correct
		// même après un `migrate:fresh --seed`.
		Permission::where('slug', 'delete-etudiant')->update([
			'nom' => 'Désactiver/réactiver un étudiant',
			'description' => 'Désactiver ou réactiver un étudiant (ce bouton ne supprime pas le dossier)',
		]);

		// Relevés de notes : uniquement create/view pour l'instant (mapping de route encore
		// ambigu entre ReleveController et ReleveNoteController, aucune page front ne les
		// consomme encore via <Can> — posés pour cohérence, non câblés).
		Permission::firstOrCreate(
			['slug' => 'create-releve'],
			['nom' => 'Générer un relevé de notes', 'description' => 'Générer un relevé de notes']
		);
		Permission::firstOrCreate(
			['slug' => 'view-releve'],
			['nom' => 'Voir un relevé de notes', 'description' => 'Voir un relevé de notes']
		);

		// Journal d'activité (audit)
		Permission::firstOrCreate(
			['slug' => 'view-logs'],
			['nom' => "Voir le journal d'activité", 'description' => "Consulter le journal d'activité de tous les utilisateurs"]
		);
		Permission::firstOrCreate(
			['slug' => 'delete-log'],
			['nom' => "Supprimer un log d'activité", 'description' => "Supprimer des entrées du journal d'activité"]
		);

		// Pages de paramétrage (années scolaires, niveaux, champs obligatoires de candidature,
		// types de diplôme, moyens de connaissance, catalogue de types de documents, support).
		$configuration = [
			'create-annee-scolaire' => 'Ajouter une année scolaire',
			'update-annee-scolaire' => 'Modifier une année scolaire',
			'create-niveau' => 'Ajouter un niveau scolaire',
			'update-niveau' => 'Modifier un niveau scolaire',
			'delete-niveau' => 'Supprimer une pièce requise pour un niveau',
			'update-candidature-field-config' => 'Modifier les champs obligatoires de candidature',
			'create-type-diplome' => 'Ajouter un type de diplôme',
			'update-type-diplome' => 'Modifier un type de diplôme',
			'delete-type-diplome' => 'Supprimer un type de diplôme',
			'create-moyen-connaissance' => 'Ajouter un moyen de connaissance',
			'update-moyen-connaissance' => 'Modifier un moyen de connaissance',
			'delete-moyen-connaissance' => 'Supprimer un moyen de connaissance',
			'create-type-document' => 'Ajouter un type de document',
			'update-type-document' => 'Modifier un type de document',
			'delete-type-document' => 'Supprimer un type de document',
			'create-ticket-support' => 'Créer un ticket de support',
			'update-ticket-support' => 'Modifier le statut/assignation d\'un ticket de support',
			'delete-message-support' => 'Supprimer un message de support',
		];
		foreach ($configuration as $slug => $nom) {
			Permission::firstOrCreate(['slug' => $slug], ['nom' => $nom, 'description' => $nom]);
		}

		// Publication : actions de bascule irréversibles, distinctes d'un simple update
		// (rendent le résultat visible aux étudiants), donc slug dédié plutôt que réutiliser update-*.
		Permission::firstOrCreate(
			['slug' => 'publish-evaluation'],
			['nom' => 'Publier une évaluation', 'description' => 'Publier une évaluation']
		);
		Permission::firstOrCreate(
			['slug' => 'publish-note'],
			['nom' => 'Publier les notes', 'description' => 'Publier les notes']
		);

		// Module Finance (dépenses, tranches/plans de paiement, paiements, négociations, recouvrement)
		// — absent du front statique d'origine (roleAccess.js n'en avait aucune notion), câblé pour
		// la première fois ici.
		$finance = [
			'create-depense' => 'Ajouter une dépense',
			'delete-depense' => 'Supprimer une dépense', // pas de route update-depense côté back
			'create-tranche-paiement' => 'Ajouter une tranche de paiement',
			'update-tranche-paiement' => 'Modifier une tranche de paiement',
			'delete-tranche-paiement' => 'Supprimer une tranche de paiement',
			'create-plan-paiement' => 'Ajouter un plan de paiement',
			'update-plan-paiement' => 'Modifier un plan de paiement',
			'delete-plan-paiement' => 'Supprimer un plan de paiement',
			'create-paiement' => 'Effectuer un paiement',
			'update-paiement' => 'Modifier un paiement',
			'create-negociation' => 'Créer une négociation',
			'update-negociation' => 'Modifier une négociation',
			'delete-negociation' => 'Supprimer une négociation',
			'create-paiement-negociation' => 'Ajouter un paiement à une négociation',
			'send-rappel-recouvrement' => 'Envoyer un rappel de recouvrement',
			'declare-abandon-etudiant' => 'Déclarer un abandon étudiant',
			'update-frais-scolarite' => 'Modifier un enregistrement de frais de scolarité',
			'delete-frais-scolarite' => 'Supprimer un enregistrement de frais de scolarité',
			'duplicate-frais-scolarite' => "Dupliquer les frais de scolarité d'une année",
			// Frais d'inscription (candidature) : ressource distincte de frais-scolarite (tuition),
			// aucune permission legacy ne la couvrait.
			'create-frais-inscription' => "Ajouter un tarif de frais d'inscription",
			'update-frais-inscription' => "Modifier un tarif de frais d'inscription",
			'delete-frais-inscription' => "Supprimer un tarif de frais d'inscription",
		];
		foreach ($finance as $slug => $nom) {
			Permission::firstOrCreate(['slug' => $slug], ['nom' => $nom, 'description' => $nom]);
		}

		// 2026-07-21 -- extension a toutes les fonctionnalites restantes de l'application
		// (jours feries, reclamations, prospects/brochures, galerie, publications/blogs,
		// partenaires, communications internes + moderation web/newsletter, generateur
		// d'examens en ligne + console de correction).
		$remaining = [
			'create-jour-ferie' => 'Ajouter un jour ferie',
			'update-jour-ferie' => 'Modifier un jour ferie',
			'delete-jour-ferie' => 'Supprimer un jour ferie',

			'update-reclamation' => "Traiter une reclamation d'etudiant",

			'update-prospect' => "Changer le statut d'un prospect",
			'delete-prospect' => 'Supprimer un prospect',

			'create-galerie-album' => 'Ajouter un album photo',
			'update-galerie-album' => 'Modifier un album photo',
			'delete-galerie-album' => 'Supprimer un album photo',
			'create-galerie-photo' => 'Ajouter une photo',
			'update-galerie-photo' => 'Modifier une photo',
			'delete-galerie-photo' => 'Supprimer une photo',

			'create-blog' => 'Ajouter une publication',
			'update-blog' => 'Modifier une publication',
			'delete-blog' => 'Supprimer une publication',
			'publish-blog' => 'Publier/depublier une publication',

			'create-partenaire' => 'Ajouter un partenaire',
			'update-partenaire' => 'Modifier un partenaire',
			'delete-partenaire' => 'Supprimer un partenaire',

			'create-communication' => 'Creer une communication interne',
			'update-communication' => 'Modifier une communication interne',
			'delete-communication' => 'Supprimer une communication interne',
			'moderate-commentaire-web' => 'Moderer/supprimer un commentaire du site public',
			'delete-abonne-newsletter' => 'Supprimer un abonne a la newsletter',

			'create-question-examen' => "Ajouter une partie/question/option d'un examen en ligne",
			'update-question-examen' => "Modifier une partie/question/option d'un examen en ligne",
			'delete-question-examen' => "Supprimer une partie/question/option d'un examen en ligne",
			'grade-examen' => 'Corriger/valider les notes des soumissions a un examen en ligne',
			'manage-exam-session' => "Gerer (modifier/supprimer/nettoyer) les sessions d'examen en ligne",
		];
		foreach ($remaining as $slug => $nom) {
			Permission::firstOrCreate(['slug' => $slug], ['nom' => $nom, 'description' => $nom]);
		}

		// 2026-07-21 (suite) -- deuxieme passe demandee explicitement par l'utilisateur :
		// etude de dossier (workflow complet de la candidature), examens/concours, presences
		// enseignant, bourses, situation etudiant, groupes/clubs/comite de classe, et le reste
		// des controleurs jamais touches par can:.
		$secondPass = [
			'valider-candidature' => 'Valider un dossier de candidature',
			'rejeter-candidature' => 'Rejeter un dossier de candidature',
			'rectifier-candidature' => 'Demander une rectification de dossier',
			'transmettre-candidature' => "Transmettre un dossier a l'academie",
			'reorienter-candidature' => 'Reorienter un candidat',
			'controler-presence-candidature' => "Controler la presence d'un candidat au concours",
			'controler-admission-candidature' => "Controler l'admission d'un candidat",
			'inscrire-etudiant-candidature' => "Inscrire un candidat en tant qu'etudiant",
			'payer-participation-candidature' => 'Enregistrer le paiement des frais de participation',
			'delete-brouillon-candidature' => "Supprimer un dossier de candidature incomplet (jamais soumis)",

			// Gestion des rôles : aucune permission n'existait pour ce module (jamais
			// seedée par aucun seeder existant) -- can:create-role/update-role/delete-role/
			// assign-role-permissions étaient donc cassés pour tout le monde, y compris les
			// rôles à accès complet (détecté via permissions:audit).
			'create-role' => 'Ajouter un rôle',
			'update-role' => 'Modifier un rôle',
			'delete-role' => 'Supprimer un rôle',
			'assign-role-permissions' => "Attribuer les permissions d'un rôle",

			'update-surveillant' => "Modifier le statut/type d'un surveillant",

			'enregistrer-presence-cours' => 'Enregistrer la presence a un cours (etudiants/enseignant, QR code)',
			'valider-seance-presence' => 'Valider/annuler une seance de cours',
			'update-comportement-etudiant' => "Modifier le comportement d'un etudiant lors d'une presence",
			'valider-justificatif-presence' => "Valider/refuser un justificatif d'absence",
			'traiter-alerte-presence' => "Traiter une alerte d'assiduite",
			'generer-conseil-classe' => 'Generer la synthese du conseil de classe',

			'create-bourse' => 'Ajouter une bourse',
			'update-bourse' => 'Modifier une bourse',
			'delete-bourse' => 'Supprimer une bourse',
			'affecter-bourse' => 'Affecter/retirer une bourse a un etudiant',

			'update-situation-etudiant' => "Modifier la situation (statut) d'un etudiant",
			'reinscrire-etudiant' => 'Reinscrire un etudiant',

			'create-club' => 'Ajouter un club',
			'update-club' => 'Modifier un club (et ses membres)',
			'delete-club' => 'Supprimer un club',

			'create-concours-session' => 'Ajouter une session de concours',
			'update-concours-session' => 'Modifier une session de concours',
			'publish-concours-session' => 'Publier/depublier une session de concours',
			'create-concours-matiere' => 'Ajouter une matiere de concours',
			'update-concours-matiere' => 'Modifier une matiere de concours',
			'delete-concours-matiere' => 'Supprimer une matiere de concours',
			'create-concours-session-matiere' => 'Associer une matiere a une session de concours',
			'update-concours-session-matiere' => 'Modifier une matiere associee a une session',
			'delete-concours-session-matiere' => 'Retirer une matiere associee a une session',
			'enregistrer-notes-concours' => 'Enregistrer les notes de concours',

			'update-configuration-site' => 'Modifier la configuration generale du site',
			'allouer-salle-evaluation' => "Allouer les salles et surveillants d'une evaluation",
			'generer-anonymat-evaluation' => "Generer/supprimer les codes d'anonymat d'une evaluation",
			'valider-absence' => 'Valider une absence en attente',

			'create-opportunite' => "Ajouter une offre/opportunite",
			'update-opportunite' => "Modifier une offre/opportunite",
			'delete-opportunite' => "Supprimer une offre/opportunite",
			'publish-opportunite' => "Publier une offre/opportunite",

			'reply-message-contact' => 'Repondre a un message de contact',
			'delete-message-contact' => 'Supprimer un message de contact',
			'delete-releve' => 'Supprimer un releve de notes',
			'annuler-paiement' => 'Annuler un paiement',

			'create-comite-classe' => 'Ajouter un comite de classe',
			'delete-comite-classe' => 'Supprimer un comite de classe',
			'update-syllabus' => "Modifier/deposer le syllabus d'une UV",
		];
		foreach ($secondPass as $slug => $nom) {
			Permission::firstOrCreate(['slug' => $slug], ['nom' => $nom, 'description' => $nom]);
		}
	}

	private function syncRolePermissions(): void
	{
		$slugs = fn (array $s) => Permission::whereIn('slug', $s)->pluck('id');
		$all = fn () => Permission::whereNotNull('slug')->pluck('id');

		// Accès complet : reprend FULL_ACCESS de l'ancien roleAccess.js
		Role::whereIn('slug', ['informaticien', 'directeur-general-adjoint', 'directeur-general'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($all()));

		// Lecture seule (finances / études) : reprend READ_ONLY
		$readOnly = $slugs(['view-user', 'view-cours', 'view-actualite']);
		Role::whereIn('slug', [
			'directeur-des-affaires-financieres',
			'responsable-administratif-et-financier',
			'directeur-des-etudes',
		])->get()->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($readOnly));

		// Logistique / direction académique : READ_ONLY + accès enseignant + académique complet + cours complet
		$academic = $slugs([
			'view-user', 'view-cours', 'view-actualite',
			'create-enseignant', 'update-enseignant', 'delete-enseignant', 'view-enseignant',
			'create-filiere', 'update-filiere', 'delete-filiere', 'view-filiere',
			'create-ue', 'update-ue', 'delete-ue', 'view-ue',
			'create-uv', 'update-uv', 'delete-uv', 'view-uv',
			'create-evaluation', 'update-evaluation', 'delete-evaluation', 'view-evaluation',
			'create-salle', 'update-salle', 'delete-salle', 'view-salle',
			'create-etudiant', 'update-etudiant', 'delete-etudiant', 'view-etudiant',
			'create-groupe', 'delete-groupe', 'view-groupe',
			'create-semestre', 'update-semestre', 'delete-semestre', 'view-semestre',
			'create-releve', 'view-releve',
			'create-actualite', 'update-actualite', 'delete-actualite',
			'create-cours', 'update-cours', 'delete-cours',
			'publish-evaluation', 'publish-note',
			'create-annee-scolaire', 'update-annee-scolaire',
			'create-niveau', 'update-niveau', 'delete-niveau',
			'create-type-diplome', 'update-type-diplome', 'delete-type-diplome',
			'update-candidature-field-config',
			'create-moyen-connaissance', 'update-moyen-connaissance', 'delete-moyen-connaissance',
			'create-type-document', 'update-type-document', 'delete-type-document',
		]);
		Role::whereIn('slug', ['logiticien-academique', 'directeur-academique'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($academic));

		// Finance : aucune contrepartie dans l'ancien roleAccess.js (le module finance n'y
		// existait pas du tout), accordé ici aux deux rôles dont l'intitulé correspond
		// (directeur des affaires financières, responsable administratif et financier) pour
		// qu'ils gardent la capacité d'usage qu'ils avaient de facto quand ces routes
		// n'étaient protégées par aucune permission.
		$finance = $slugs([
			'create-depense', 'delete-depense',
			'create-tranche-paiement', 'update-tranche-paiement', 'delete-tranche-paiement',
			'create-plan-paiement', 'update-plan-paiement', 'delete-plan-paiement',
			'create-paiement', 'update-paiement',
			'create-negociation', 'update-negociation', 'delete-negociation', 'create-paiement-negociation',
			'send-rappel-recouvrement', 'declare-abandon-etudiant',
			'create-frais-scolarite', 'update-frais-scolarite', 'delete-frais-scolarite', 'duplicate-frais-scolarite',
			'create-frais-inscription', 'update-frais-inscription', 'delete-frais-inscription',
		]);
		Role::whereIn('slug', ['directeur-des-affaires-financieres', 'responsable-administratif-et-financier'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($finance));

		// Marketing : READ_ONLY + lecture seule sur le reste de l'académique
		$marketingView = $slugs([
			'view-user', 'view-cours', 'view-actualite',
			'view-filiere', 'view-ue', 'view-uv', 'view-evaluation',
			'view-salle', 'view-etudiant', 'view-groupe', 'view-semestre', 'view-releve',
		]);
		Role::where('slug', 'responsable-marketing')
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($marketingView));

		// 2026-07-21 -- roles pour les domaines etendus la meme session (jours feries,
		// reclamations, prospects, galerie, blogs, partenaires, communications, examens en ligne).
		Role::whereIn('slug', ['logiticien-academique', 'directeur-academique'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs([
				'create-jour-ferie', 'update-jour-ferie', 'delete-jour-ferie',
				'update-reclamation',
				'create-question-examen', 'update-question-examen', 'delete-question-examen',
				'grade-examen', 'manage-exam-session',
			])));

		// Enseignants : construisent et corrigent leurs propres examens en ligne (pas de
		// verification de propriete cote route pour l'instant -- meme granularite que le reste).
		Role::where('slug', 'enseignant')
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs([
				'create-question-examen', 'update-question-examen', 'delete-question-examen',
				'grade-examen', 'manage-exam-session',
			])));

		// Marketing : gestion complete des contenus vitrine/communication (prospects, galerie,
		// blogs, partenaires, communications internes, moderation web + newsletter).
		$marketingManage = $slugs([
			'update-prospect', 'delete-prospect',
			'create-galerie-album', 'update-galerie-album', 'delete-galerie-album',
			'create-galerie-photo', 'update-galerie-photo', 'delete-galerie-photo',
			'create-blog', 'update-blog', 'delete-blog', 'publish-blog',
			'create-partenaire', 'update-partenaire', 'delete-partenaire',
			'create-communication', 'update-communication', 'delete-communication',
			'moderate-commentaire-web', 'delete-abonne-newsletter',
		]);
		Role::where('slug', 'responsable-marketing')
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($marketingManage));

		// Chargé de la clientèle / collaborateur commercial : suivent et qualifient les prospects.
		Role::whereIn('slug', ['charge-de-la-clientele', 'collaborateur-commercial'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs(['update-prospect', 'delete-prospect'])));

		// 2026-07-21 (suite) -- roles pour la deuxieme passe.
		// Chargé de la clientèle : responsable du traitement des dossiers de candidature
		// (etude de dossier) avant transmission a l'academie.
		Role::where('slug', 'charge-de-la-clientele')
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs([
				'valider-candidature', 'rejeter-candidature', 'rectifier-candidature', 'transmettre-candidature',
				'reorienter-candidature', 'controler-presence-candidature',
				'controler-admission-candidature', 'payer-participation-candidature',
				'reply-message-contact', 'delete-message-contact', 'delete-brouillon-candidature',
			])));

		// Académique : suite du traitement du dossier après transmission, gestion des
		// concours (sessions/matieres/notes), salles/anonymat d'evaluation, clubs, comite
		// de classe, bourses, situation etudiant, reinscription.
		Role::whereIn('slug', ['logiticien-academique', 'directeur-academique'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs([
				'valider-candidature', 'rejeter-candidature', 'rectifier-candidature',
				'reorienter-candidature', 'inscrire-etudiant-candidature', 'delete-brouillon-candidature',
				'create-concours-session', 'update-concours-session', 'publish-concours-session',
				'create-concours-matiere', 'update-concours-matiere', 'delete-concours-matiere',
				'create-concours-session-matiere', 'update-concours-session-matiere', 'delete-concours-session-matiere',
				'enregistrer-notes-concours', 'allouer-salle-evaluation', 'generer-anonymat-evaluation',
				'create-club', 'update-club', 'delete-club',
				'create-comite-classe', 'delete-comite-classe',
				'create-bourse', 'update-bourse', 'delete-bourse', 'affecter-bourse',
				'update-situation-etudiant', 'reinscrire-etudiant', 'update-groupe',
				'update-surveillant', 'valider-absence',
			])));

		// Surveillants et enseignants : suivi des présences, comportement, justificatifs,
		// alertes et conseils de classe au quotidien.
		Role::whereIn('slug', ['surveillant', 'enseignant'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs([
				'enregistrer-presence-cours', 'valider-seance-presence', 'update-comportement-etudiant',
				'valider-justificatif-presence', 'traiter-alerte-presence', 'generer-conseil-classe',
			])));
		Role::where('slug', 'enseignant')
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs(['update-syllabus'])));

		// Finance : bourses et annulation de paiement (pas encore couvert par le bundle finance existant).
		Role::whereIn('slug', ['directeur-des-affaires-financieres', 'responsable-administratif-et-financier'])
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs([
				'create-bourse', 'update-bourse', 'delete-bourse', 'affecter-bourse', 'annuler-paiement',
			])));

		// 2026-07-21 (suite) -- l'enseignant peut creer/modifier ses propres evaluations et
		// notes (page "mes examens"), mais pas les supprimer -- suppression reservee au staff
		// academique (plus destructeur : efface aussi les notes deja saisies par d'autres).
		// Aucune verification de propriete cote controleur pour l'instant (meme limite deja
		// acceptee pour update-syllabus et grade-examen) -- validé explicitement avec l'utilisateur.
		Role::where('slug', 'enseignant')
			->get()
			->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($slugs([
				'create-evaluation', 'update-evaluation', 'create-note', 'update-note',
			])));
	}
}
