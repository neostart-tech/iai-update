<?php

namespace App\Http\Controllers;

use App\Enums\TypeDiplomeEnum;
use App\Http\Requests\Candidature\StoreRequest;
use App\Http\Resources\CandidatureResource;
use App\Notifications\Candidatures\CandidatAbsentNotification;
use App\Notifications\Candidatures\CandidatPresentNotification;
use App\Jobs\{CandidatureFraisPayementJob, ConcoursResultJob, SmsSendingProcess};
use App\Models\{AnneeScolaire, Bourse, BourseEtudiant, Candidature, CandidatureDocument, CandidatureFieldConfig, Echeance, Etudiant, FiliereGroup, FraisEtudiant, FraisInscription, FraisScolarite, Group, Inscription, Paiement, Reorientation, Role, TranchePaiement, TypeDiplome, TypeDiplomeChamp};
use App\Notifications\Candidatures\CandidatWelcomeNotification;
use App\Traits\ActionsTraits\{IndexTrait, CandidatureFirstValidationTrait};
use App\Traits\FileManagementTrait;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Notifications\Candidatures\CandidatAccountLockNotification;
use App\Notifications\Candidatures\CandidatToEtudiantWelcomeNotification;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, DB, Hash};
use Illuminate\Support\Str;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade;
use Sabberworm\CSS\Rule\Rule;
use Throwable;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Candidatures\NewCandidatureSubmittedNotification;

class CandidatureController extends Controller
{
	use FileManagementTrait;

	use CandidatureFirstValidationTrait, IndexTrait;

	private const LETTRE = 'lettres_manuscrites';
	private const NAISSANCE = 'naissances';
	private const NATIONALITE = 'nationalites';
	private const DIPLOME = 'diplomes';
	private const PHOTO_IDENTITE = 'photos_identite';
	private const CERTIFICAT_MEDICAL = 'certificats_medicaux';
	private const COUPON = 'coupons';
	private const BULLETINS_LYCEE = 'bulletins_lycee';
	private const RELEVE_BAC_1 = 'releve_bac_1';
	private const RELEVE_BAC_2 = 'releve_bac_2';


	public function show(Candidature $candidature)
	{
		$candidature->load(['album', 'tuteur', 'tuteurs', 'responsable', 'niveau', 'filiere', 'advertiser', 'submittedDocuments', 'concoursSession', 'typeDiplome', 'moyenConnaissance']);

		$requirements = [];
		if ($candidature->niveau_id) {
			$requirements = \App\Models\DocumentRequirement::with('documentType')->where('niveau_id', $candidature->niveau_id)
				->where(function ($q) use ($candidature) {
					$q->whereNull('filiere_id')->orWhere('filiere_id', $candidature->filiere_id);
				})->get();
		}

		$originalAlbum = $candidature->album ? $candidature->album->toArray() : [];
		$albumFiles = [];
		foreach ($candidature->submittedDocuments as $doc) {
			$albumFiles[$doc->document_key] = $doc->file_path;
		}

		unset($candidature->album);
		$candidature->setAttribute('album', (object) array_merge($originalAlbum, $albumFiles));

        $activeAnnee = \App\Models\AnneeScolaire::where('active', true)->first();
        $year = $activeAnnee && $activeAnnee->date_debut ? \Carbon\Carbon::parse($activeAnnee->date_debut)->year : today()->year;
        
        $candidature->setAttribute('next_matricule', \App\Models\Etudiant::generateNextMatricule($year));
        
        $activeAnneeData = null;
        $fraisScolariteAttendu = 0;
        
        if ($activeAnnee) {
            $activeAnneeData = [
                'id' => $activeAnnee->id,
                'nom' => $activeAnnee->nom,
                'date_debut' => $activeAnnee->date_debut
            ];
            
            $fraisScolarite = \App\Models\FraisScolarite::getFraisForEtudiant(
                $candidature->niveau_id,
                $candidature->genre,
                $candidature->filiere_id,
                $activeAnnee->id,
                $candidature->mode_formation ?: 'Tous'
            );
            $fraisScolariteAttendu = $fraisScolarite ? (float) $fraisScolarite->montant : 0;
        }
        
        $candidature->setAttribute('active_annee_scolaire', $activeAnneeData);
        $candidature->setAttribute('frais_scolarite_attendu', $fraisScolariteAttendu);
        
        $emailDomain = \App\Models\Configuration::where('key', 'email_domain')->value('value') ?: 'escen.university';
        $candidature->setAttribute('email_domain', $emailDomain);

		if (request()->ajax() || request()->wantsJson()) {
			return response()->json([
				'data' => $candidature,
				'expected_docs' => $requirements
			]);
		}

		return view('admin.candidatures.show', compact('candidature'))->with([
			'album' => (object) $album,
			'niveau' => $candidature->niveau,
			'filiere' => $candidature->filiere,
			'filieres' => Filiere::all(),
			'niveaux' => Niveau::all(),
		]);
	}

	public function generateMatricule($year)
	{
		return response()->json([
			'matricule' => Etudiant::generateNextMatricule((int)$year)
		]);
	}

	public function create(): View
	{
		$documentRequirements = \App\Models\DocumentRequirement::with('documentType')
			->get()
			->map(fn ($req) => [
				'niveau_id' => $req->niveau_id,
				'filiere_id' => $req->filiere_id,
				'is_obligatoire' => (bool) $req->is_obligatoire,
				'document_key' => $req->documentType?->document_key,
				'nom_affichage' => $req->documentType?->nom_affichage,
				'is_multiple' => (bool) ($req->documentType?->is_multiple ?? false),
				'accepted_formats' => $req->documentType?->accepted_formats ?? 'all',
				'description' => $req->description,
			])
			->filter(fn ($req) => $req['document_key'] !== null)
			->values();

		// Le concours d'admission ne concerne actuellement que la Licence 1 : le candidat
		// ne choisit ni son niveau ni sa filière, ils sont déduits automatiquement ici
		// (Licence 1 -> son groupe -> l'unique filière rattachée à ce groupe).
		$niveauCandidature = Niveau::where('libelle', 'Licence 1')->first();
		$filiereCandidature = $niveauCandidature
			? Filiere::whereHas('groups', fn ($q) => $q->where('niveau_id', $niveauCandidature->id))->first()
			: null;

		// Configuration des champs (par école) : quels champs simples sont affichés/obligatoires,
		// et quels types de diplôme (+ leurs champs de parcours scolaire) proposer. Seuls les
		// champs avec `afficher = true` sont transmis à la vue — leur simple présence dans
		// $champsConfig suffit donc à décider s'il faut afficher le bloc correspondant.
		$champsConfig = CandidatureFieldConfig::where('afficher', true)->get()->keyBy('champ_key');
		$typesDiplome = TypeDiplome::actifs()->with('champs:id,type_diplome_id,champ_key,obligatoire')->get(['id', 'nom', 'ordre']);
		$moyensConnaissance = \App\Models\MoyenConnaissance::actifs()->get(['id', 'libelle']);

		return view('candidatures.create')->with([
			'candidature' => new Candidature(),
			'countries' => CountryListFacade::getList(config('app.locale')),
			'documentRequirements' => $documentRequirements,
			'niveauCandidatureId' => $niveauCandidature?->id,
			'filiereCandidatureId' => $filiereCandidature?->id,
			'champsConfig' => $champsConfig,
			'typesDiplome' => $typesDiplome,
			'moyensConnaissance' => $moyensConnaissance,
			'sigleEtablissement' => \App\Helpers\ConfigHelper::getSigle(),
		]);
	}

	/**
	 * Construit dynamiquement les règles de validation des champs "libres"
	 * (configurables par l'école via /parametre/champs-obligatoires et
	 * /parametre/types-diplome) à ajouter aux règles fixes du dossier.
	 *
	 * Champs du parcours scolaire (mention_bac, serie, numero_table,
	 * etablissement_diplome, annee_bac) : si la requête fournit un
	 * `type_diplome_id`, on applique la configuration de ce type précis.
	 * Sinon (formulaire pas encore mis à jour pour envoyer ce champ), on
	 * reproduit exactement l'ancien comportement figé, pour ne rien casser :
	 * mention/série/numéro de table/année obligatoires, établissement libre.
	 */
	private function reglesChampsConfigurables(Request $request, ?Candidature $candidature = null): array
	{
		$rules = [];

		$configs = CandidatureFieldConfig::get()->keyBy('champ_key');

		// Un champ non affiché ne peut jamais être exigé, quelle que soit la valeur
		// de `obligatoire` enregistrée en base pour lui.
		$estObligatoire = function (string $champKey, bool $defaut = false) use ($configs) {
			$config = $configs[$champKey] ?? null;
			if (!$config) return $defaut;
			return $config->afficher && $config->obligatoire;
		};

		$champsSimples = [
			'nom_jeune_fille' => ['string', 'max:255'],
			'tel2' => ['string'],
			'tel3' => ['string'],
			'bp' => ['string', 'max:255'],
			'fax' => ['string', 'max:255'],
			'adresse' => ['string', 'max:255'],
		];
		foreach ($champsSimples as $champ => $extra) {
			$rules[$champ] = [...($estObligatoire($champ) ? ['required'] : ['nullable']), ...$extra];
		}

		// Traité à part (règle `unique` en plus du string/max), pas dans la boucle
		// générique ci-dessus : deux dossiers ne doivent jamais partager le même bordereau.
		$rules['numero_bordereau'] = [
			...($estObligatoire('numero_bordereau') ? ['required'] : ['nullable']),
			'string',
			'max:50',
			\Illuminate\Validation\Rule::unique('candidatures', 'numero_bordereau')->ignore($candidature?->id),
		];

		// "Comment avez-vous connu {sigle} ?" : select alimenté par la liste des
		// moyens de connaissance gérée dans Paramètres > Moyens de connaissance.
		$rules['moyen_connaissance_id'] = [
			...($estObligatoire('comment_connu_ecole') ? ['required'] : ['nullable']),
			'exists:moyens_connaissances,id',
		];
		$rules['moyen_connaissance_precision'] = ['nullable', 'string', 'max:255'];

		$champsTuteur = [
			'nom' => true,
			'prenom' => true,
			'profession' => false,
			'employeur' => false,
			'email' => false,
			'tel' => false,
			'adresse' => false,
		];
		foreach ($champsTuteur as $champ => $defaut) {
			// tuteur_nom/tuteur_prenom : jamais masqués, donc pas de vérification `afficher`,
			// mais leur `obligatoire` stocké reste respecté comme avant.
			$obligatoire = in_array($champ, ['nom', 'prenom'])
				? (bool) ($configs['tuteur_' . $champ]->obligatoire ?? $defaut)
				: $estObligatoire('tuteur_' . $champ, $defaut);
			$rules["tuteurs.*.$champ"] = [$obligatoire ? 'required' : 'nullable', 'string'];
		}

		$typeDiplomeId = $request->input('type_diplome_id');
		if ($typeDiplomeId) {
			$champsDuType = TypeDiplomeChamp::where('type_diplome_id', $typeDiplomeId)->pluck('obligatoire', 'champ_key');
			foreach (TypeDiplomeChamp::CHAMPS_DISPONIBLES as $champ => $label) {
				$rules[$champ] = isset($champsDuType[$champ])
					? [$champsDuType[$champ] ? 'required' : 'nullable']
					: ['nullable']; // champ non configuré pour ce diplôme => non exigé
			}
		} else {
			// Rétrocompatibilité : aucun type de diplôme transmis, on garde le
			// comportement historique figé plutôt que de tout rendre optionnel.
			$rules['mention_bac'] = ['required', 'string'];
			$rules['serie'] = ['required', 'string'];
			$rules['numero_table'] = ['required', 'digits_between:1,7'];
			$rules['annee_bac'] = ['required', 'integer', 'min:1990', 'max:' . date('Y')];
			$rules['etablissement_diplome'] = ['nullable', 'string'];
		}

		return $rules;
	}

	/**
	 * En mode concours, une candidature doit obligatoirement être rattachée à une
	 * session de concours ouverte pour l'année scolaire en cours — sinon elle reste
	 * orpheline (concours_session_id = null) et bascule silencieusement en admission
	 * directe sans jamais passer par le paiement, le contrôle de présence ou les
	 * notes de concours. On bloque donc l'inscription tant qu'aucune session ouverte
	 * n'existe, plutôt que de laisser passer un dossier mal rattaché.
	 */
	private function sessionConcoursIndisponible(): bool
	{
		if (!\App\Helpers\ConfigHelper::isModeConcoursActif()) {
			return false;
		}

		return !\App\Models\ConcoursSession::where('annee_scolaire_id', getAnneeScolaireId())
			->where('statut', 'ouvert')
			->exists();
	}

	public function store(Request $request)
	{
		if ($this->sessionConcoursIndisponible()) {
			$message = "Les inscriptions au concours ne sont pas encore ouvertes. Veuillez réessayer plus tard.";

			if ($request->wantsJson()) {
				return response()->json(['success' => false, 'message' => $message], 422);
			}

			return redirect()->back()->withErrors(['email' => $message])->withInput();
		}

		$anneeScolaireId = getAnneeScolaireId();

		// 1. Vérification d'unicité de candidature par année scolaire
		$exists = Candidature::where('email', $request->email)
			->where('annee_scolaire_id', $anneeScolaireId)
			->exists();

		if ($exists) {
			$message = "Vous avez déjà déposé une candidature pour cette année scolaire.";

			// Le site Nuxt consomme aussi cette route en JSON : on garde ce format pour
			// lui inchangé. Le formulaire public HTML, lui, doit revenir sur la page avec
			// une erreur affichée proprement au lieu d'un JSON brut affiché tel quel.
			if ($request->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => $message
				], 422);
			}

			return redirect()->back()->withErrors(['email' => $message])->withInput();
		}

		// 2. Vérification si l'étudiant est déjà inscrit (optionnel mais recommandé)
		if (Etudiant::where('email', $request->email)->exists()) {
			$message = "Un compte étudiant existe déjà avec cet email. Veuillez passer par la procédure de réinscription.";

			if ($request->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => $message
				], 422);
			}

			return redirect()->back()->withErrors(['email' => $message])->withInput();
		}

		// 2bis. Vérification des champs obligatoires du dossier — les champs fixes
		// (identité) restent toujours requis ; les champs configurables (parcours
		// scolaire selon le type de diplôme, tuteur, coordonnées secondaires...)
		// suivent la configuration de l'école, voir reglesChampsConfigurables().
		$request->validate([
			'nom' => ['required', 'string', 'max:255'],
			'prenom' => ['required', 'string', 'max:255'],
			'genre' => ['required', 'string'],
			'date_naissance' => ['required', 'date'],
			'lieu_naissance' => ['required', 'string', 'max:255'],
			'nationalite' => ['required', 'string'],
			'tel' => ['required', 'string'],
			'email' => ['required', 'email', 'max:255'],
			'accept_cgu' => ['accepted'],
			...$this->reglesChampsConfigurables($request),
		], [
			'annee_bac.max' => "L'année du BAC ne peut pas être postérieure à l'année en cours (" . date('Y') . ").",
			'numero_table.digits_between' => "Le numéro de table doit contenir uniquement des chiffres (7 maximum).",
			'accept_cgu.accepted' => "Vous devez accepter les conditions générales d'utilisation.",
		]);

		// 3. Création du candidat, de ses tuteurs/responsable et de ses documents dans une
		// même transaction : si la validation des pièces jointes (dans updateOrCreateAlbum)
		// échoue, tout est annulé plutôt que de laisser une candidature orpheline sans
		// documents en base (ce qui bloquait ensuite tout nouveau dépôt avec le même email).
		$candidat = DB::transaction(function () use ($request, &$plainPassword) {
			$typeDiplome = $request->filled('type_diplome_id')
				? \App\Models\TypeDiplome::find($request->input('type_diplome_id'))
				: null;

			$candidat = Candidature::create([
				...$request->only([
					'nom',
					'prenom',
					'nom_jeune_fille',
					'numero_table',
					'annee_bac',
					'mention_bac',
					'serie',
					'etablissement_diplome',
					'genre',
					'date_naissance',
					'lieu_naissance',
					'email',
					'nationalite',
					'hobbit',
					'tel',
					'tel2',
					'tel3',
					'bp',
					'fax',
					'niveau_id',
					'filiere_id',
					'adresse',
					'numero_bordereau',
					'moyen_connaissance_id',
					'moyen_connaissance_precision',
				]),
				...injectAnneeScolaireId(),
				'concours_session_id' => \App\Models\ConcoursSession::where('annee_scolaire_id', getAnneeScolaireId())->value('id'),
				'password' => Hash::make($plainPassword = Str::random(8)),
				'code' => fake()->unique()->numberBetween(9999, 100000),
				// Ce flux crée le dossier complet en un seul envoi (pas de brouillon
				// intermédiaire comme l'inscription en plusieurs étapes) : marqué soumis
				// immédiatement, sinon il resterait invisible sur les listes filtrées.
				'soumis_le' => now(),
				// `type_diplome_id` pilote la validation des champs du parcours scolaire ;
				// `dernier_diplome` (colonne texte historique) reste renseignée en
				// parallèle pour tout le code existant qui la lit encore telle quelle.
				'type_diplome_id' => $typeDiplome?->id,
				'dernier_diplome' => $typeDiplome?->nom,
			]);

			// Création des tuteurs/parents (un ou plusieurs) et, pour compatibilité avec le
			// reste de l'application (espace candidat, admission), du responsable des frais.
			$tuteursValides = [];
			foreach ($request->input('tuteurs', []) as $tuteurEntry) {
				if (blank($tuteurEntry['nom'] ?? null) && blank($tuteurEntry['prenom'] ?? null)) {
					continue;
				}

				$donneesTuteur = [
					'nom' => $tuteurEntry['nom'] ?? null,
					'prenom' => $tuteurEntry['prenom'] ?? null,
					'profession' => $tuteurEntry['profession'] ?? null,
					'employeur' => $tuteurEntry['employeur'] ?? null,
					'email' => $tuteurEntry['email'] ?? null,
					'tel' => $tuteurEntry['tel'] ?? null,
					'adresse' => $tuteurEntry['adresse'] ?? null,
					'responsable_des_frais' => filter_var($tuteurEntry['responsable_des_frais'] ?? false, FILTER_VALIDATE_BOOLEAN),
				];

				$candidat->tuteurs()->create($donneesTuteur);
				$tuteursValides[] = $donneesTuteur;
			}

			if (!empty($tuteursValides)) {
				$responsableData = collect($tuteursValides)->firstWhere('responsable_des_frais', true) ?? $tuteursValides[0];
				$candidat->responsable()->create(collect($responsableData)->except('responsable_des_frais')->all());
			}

			// Création de l'album (utilise la version corrigée ci-dessus)
			$this->updateOrCreateAlbum($request, $candidat);

			return $candidat;
		});

		// 5. Connexion et notification
		Auth::guard('web_candidatures')->login($candidat);

		$message = $candidat->greeting(true);
		$message .= ", votre dossier de candidature a été déposé avec succès.";

		$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message, $plainPassword));

		$this->notifierResponsablesNouvelleCandidature($candidat);

		if ($request->wantsJson()) {
			return response()->json([
				'success' => true,
				'message' => 'Le dossier a été déposé avec succès.',
				'candidat' => $candidat
			], 201);
		}

		return redirect()->route('candidatures.merci', ['prenom' => $candidat->prenom, 'email' => $candidat->email]);
	}

	public function merci(Request $request): View
	{
		return view('candidatures.merci', [
			'prenom' => $request->query('prenom'),
			'email' => $request->query('email'),
		]);
	}

	public function storeByAdmin(Request $request)
	{
		if ($this->sessionConcoursIndisponible()) {
			return response()->json([
				'success' => false,
				'message' => "Les inscriptions au concours ne sont pas encore ouvertes. Veuillez créer et ouvrir une session de concours pour l'année scolaire en cours avant d'enregistrer une candidature.",
			], 422);
		}

		// Mêmes informations et mêmes règles que le formulaire public (store()), à la
		// différence près que l'admin choisit lui-même le niveau (le frontend déduit la
		// filière automatiquement à partir de ce choix, au lieu du "toujours Licence 1"
		// du formulaire public).
		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
			'nom' => ['required', 'string', 'max:255'],
			'prenom' => ['required', 'string', 'max:255'],
			'genre' => ['required', 'string'],
			'date_naissance' => ['required', 'date'],
			'lieu_naissance' => ['required', 'string', 'max:255'],
			'nationalite' => ['required', 'string'],
			'tel' => ['required', 'string'],
			'email' => ['required', 'email', 'max:255', 'unique:candidatures,email'],
			'niveau_id' => ['required', 'exists:niveaux,id'],
			'filiere_id' => ['nullable', 'exists:filieres,id'],
			...$this->reglesChampsConfigurables($request),
		], [
			'numero_table.digits_between' => "Le numéro de table doit contenir uniquement des chiffres (7 maximum).",
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'errors' => $validator->errors()
			], 422);
		}

		$typeDiplome = $request->filled('type_diplome_id')
			? \App\Models\TypeDiplome::find($request->input('type_diplome_id'))
			: null;

		$candidat = Candidature::create([
			...$request->only([
				'nom',
				'prenom',
				'nom_jeune_fille',
				'numero_table',
				'annee_bac',
				'mention_bac',
				'serie',
				'etablissement_diplome',
				'genre',
				'date_naissance',
				'lieu_naissance',
				'email',
				'nationalite',
				'tel',
				'tel2',
				'tel3',
				'niveau_id',
				'filiere_id',
				'adresse',
				'numero_bordereau',
				'moyen_connaissance_id',
				'moyen_connaissance_precision',
			]),
			...injectAnneeScolaireId(),
			'concours_session_id' => \App\Models\ConcoursSession::where('annee_scolaire_id', getAnneeScolaireId())->value('id'),
			'password' => Hash::make($plainPassword = Str::random(8)),
			// Comme store() : dossier complet en un seul envoi, marqué soumis immédiatement.
			'soumis_le' => now(),
			'type_diplome_id' => $typeDiplome?->id,
			'dernier_diplome' => $typeDiplome?->nom,
			'code' => fake()->unique()->numberBetween(9999, 100000)
		]);

		// Tuteurs répétables + responsable des frais — même logique que store().
		$tuteursValides = [];
		foreach ($request->input('tuteurs', []) as $tuteurEntry) {
			if (blank($tuteurEntry['nom'] ?? null) && blank($tuteurEntry['prenom'] ?? null)) {
				continue;
			}

			$donneesTuteur = [
				'nom' => $tuteurEntry['nom'] ?? null,
				'prenom' => $tuteurEntry['prenom'] ?? null,
				'profession' => $tuteurEntry['profession'] ?? null,
				'employeur' => $tuteurEntry['employeur'] ?? null,
				'email' => $tuteurEntry['email'] ?? null,
				'tel' => $tuteurEntry['tel'] ?? null,
				'adresse' => $tuteurEntry['adresse'] ?? null,
				'responsable_des_frais' => filter_var($tuteurEntry['responsable_des_frais'] ?? false, FILTER_VALIDATE_BOOLEAN),
			];

			$candidat->tuteurs()->create($donneesTuteur);
			$tuteursValides[] = $donneesTuteur;
		}

		if (!empty($tuteursValides)) {
			$responsableData = collect($tuteursValides)->firstWhere('responsable_des_frais', true) ?? $tuteursValides[0];
			$candidat->responsable()->create(collect($responsableData)->except('responsable_des_frais')->all());
		}

		// Documents (dynamiques, même système que store()).
		$this->updateOrCreateAlbum($request, $candidat);

		// 5. Connexion et notification
		if ($request->hasSession()) {
			Auth::guard('web_candidatures')->login($candidat);
		}

		$message = $candidat->greeting(true);
		$message .= ", votre dossier de candidature a été déposé avec succès.";

		$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message, $plainPassword));

		// Notifier les administrateurs et responsables
		$this->notifierResponsablesNouvelleCandidature($candidat);

		return response()->json([
			'success' => true,
			'message' => 'Le dossier a été déposé avec succès par l\'administration.',
			'candidat' => $candidat
		], 201);
	}

	/**
	 * Inscription en plusieurs étapes (escen-website) : le dossier est créé dès
	 * l'étape 1 (identité + coordonnées, l'email étant déjà disponible à ce stade
	 * une fois les étapes fusionnées côté front) puis complété par les endpoints
	 * ci-dessous. `draft_token` (aléatoire, distinct du `slug` dérivé du nom et
	 * donc devinable) permet au candidat de reprendre son dossier sans être
	 * authentifié, sans exposer d'identifiant prévisible.
	 */
	public function soumettreEtape1(Request $request)
	{
		if ($this->sessionConcoursIndisponible()) {
			return response()->json([
				'success' => false,
				'message' => "Les inscriptions au concours ne sont pas encore ouvertes. Veuillez réessayer plus tard.",
			], 422);
		}

		if (Candidature::where('email', $request->input('email'))->exists()) {
			return response()->json([
				'success' => false,
				'message' => "Vous avez déjà déposé une candidature avec cet email.",
			], 422);
		}

		if (Etudiant::where('email', $request->input('email'))->exists()) {
			return response()->json([
				'success' => false,
				'message' => "Un compte étudiant existe déjà avec cet email. Veuillez passer par la procédure de réinscription.",
			], 422);
		}

		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
			'nom' => ['required', 'string', 'max:255'],
			'prenom' => ['required', 'string', 'max:255'],
			'genre' => ['required', 'string'],
			'date_naissance' => ['required', 'date'],
			'lieu_naissance' => ['required', 'string', 'max:255'],
			'nationalite' => ['required', 'string'],
			'tel' => ['required', 'string'],
			'email' => ['required', 'email', 'max:255'],
			'nom_jeune_fille' => ['nullable', 'string', 'max:255'],
			'tel2' => ['nullable', 'string'],
			'tel3' => ['nullable', 'string'],
			'adresse' => ['nullable', 'string', 'max:255'],
			'bp' => ['nullable', 'string', 'max:255'],
			'fax' => ['nullable', 'string', 'max:255'],
			'numero_bordereau' => ['nullable', 'string', 'max:50', 'unique:candidatures,numero_bordereau'],
			'moyen_connaissance_id' => ['nullable', 'exists:moyens_connaissances,id'],
			'moyen_connaissance_precision' => ['nullable', 'string', 'max:255'],
		]);

		if ($validator->fails()) {
			return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
		}

		$candidat = Candidature::create([
			...$request->only([
				'nom', 'prenom', 'nom_jeune_fille', 'genre', 'date_naissance', 'lieu_naissance',
				'nationalite', 'tel', 'tel2', 'tel3', 'email', 'adresse', 'bp', 'fax',
				'numero_bordereau', 'moyen_connaissance_id', 'moyen_connaissance_precision',
			]),
			...injectAnneeScolaireId(),
			'concours_session_id' => \App\Models\ConcoursSession::where('annee_scolaire_id', getAnneeScolaireId())->value('id'),
			'draft_token' => Str::random(48),
			'password' => Hash::make(Str::random(8)),
			'code' => fake()->unique()->numberBetween(9999, 100000),
		]);

		return response()->json([
			'success' => true,
			'draft_token' => $candidat->draft_token,
		], 201);
	}

	/**
	 * Mise à jour de l'étape 1 pour un dossier déjà créé (le candidat est revenu en
	 * arrière puis a corrigé quelque chose avant de continuer) — sans ça, renvoyer
	 * ces mêmes données à soumettreEtape1() échouerait sur son propre email, déjà
	 * pris par le brouillon qu'il est justement en train de modifier.
	 */
	public function mettreAJourEtape1(Request $request, string $draftToken)
	{
		try {
			$candidat = $this->candidatureParDraftToken($draftToken);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(['success' => false, 'message' => "Dossier introuvable."], 404);
		}

		if (Candidature::where('email', $request->input('email'))->where('id', '!=', $candidat->id)->exists()) {
			return response()->json([
				'success' => false,
				'message' => "Cet email est déjà utilisé par une autre candidature.",
			], 422);
		}

		if (Etudiant::where('email', $request->input('email'))->exists()) {
			return response()->json([
				'success' => false,
				'message' => "Un compte étudiant existe déjà avec cet email. Veuillez passer par la procédure de réinscription.",
			], 422);
		}

		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
			'nom' => ['required', 'string', 'max:255'],
			'prenom' => ['required', 'string', 'max:255'],
			'genre' => ['required', 'string'],
			'date_naissance' => ['required', 'date'],
			'lieu_naissance' => ['required', 'string', 'max:255'],
			'nationalite' => ['required', 'string'],
			'tel' => ['required', 'string'],
			'email' => ['required', 'email', 'max:255'],
			'nom_jeune_fille' => ['nullable', 'string', 'max:255'],
			'tel2' => ['nullable', 'string'],
			'tel3' => ['nullable', 'string'],
			'adresse' => ['nullable', 'string', 'max:255'],
			'bp' => ['nullable', 'string', 'max:255'],
			'fax' => ['nullable', 'string', 'max:255'],
			'numero_bordereau' => [
				'nullable', 'string', 'max:50',
				\Illuminate\Validation\Rule::unique('candidatures', 'numero_bordereau')->ignore($candidat->id),
			],
			'moyen_connaissance_id' => ['nullable', 'exists:moyens_connaissances,id'],
			'moyen_connaissance_precision' => ['nullable', 'string', 'max:255'],
		]);

		if ($validator->fails()) {
			return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
		}

		$candidat->update($request->only([
			'nom', 'prenom', 'nom_jeune_fille', 'genre', 'date_naissance', 'lieu_naissance',
			'nationalite', 'tel', 'tel2', 'tel3', 'email', 'adresse', 'bp', 'fax',
			'numero_bordereau', 'moyen_connaissance_id', 'moyen_connaissance_precision',
		]));

		return response()->json(['success' => true]);
	}

	private function candidatureParDraftToken(string $draftToken): Candidature
	{
		return Candidature::where('draft_token', $draftToken)
			->whereNull('soumis_le') // un dossier déjà soumis n'est plus modifiable par ces endpoints publics
			->firstOrFail();
	}

	/**
	 * Reprise d'un dossier en cours de constitution après un rechargement de page
	 * (le jeton survit en localStorage côté front, mais pas les champs saisis) —
	 * renvoie l'état actuel du dossier pour que le formulaire se re-remplisse et
	 * reprenne à la bonne étape, sans jamais exposer le mot de passe (même hashé).
	 */
	public function recupererBrouillon(string $draftToken)
	{
		try {
			$candidat = $this->candidatureParDraftToken($draftToken);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(['success' => false, 'message' => "Dossier introuvable."], 404);
		}

		return response()->json([
			'success' => true,
			'candidat' => $candidat->only([
				'nom', 'prenom', 'nom_jeune_fille', 'genre', 'date_naissance', 'lieu_naissance',
				'nationalite', 'numero_bordereau', 'moyen_connaissance_id', 'moyen_connaissance_precision', 'tel', 'tel2', 'tel3', 'email',
				'type_diplome_id', 'numero_table', 'annee_bac', 'mention_bac', 'serie', 'etablissement_diplome',
				'niveau_id', 'filiere_id',
			]),
		]);
	}

	public function soumettreEtape2Bac(Request $request, string $draftToken)
	{
		try {
			$candidat = $this->candidatureParDraftToken($draftToken);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(['success' => false, 'message' => "Dossier introuvable."], 404);
		}

		$typeDiplome = $request->filled('type_diplome_id')
			? \App\Models\TypeDiplome::find($request->input('type_diplome_id'))
			: null;

		$rules = ['type_diplome_id' => ['nullable', 'exists:type_diplomes,id']];
		if ($typeDiplome) {
			$champsDuType = TypeDiplomeChamp::where('type_diplome_id', $typeDiplome->id)->pluck('obligatoire', 'champ_key');
			foreach (TypeDiplomeChamp::CHAMPS_DISPONIBLES as $champ => $label) {
				$rules[$champ] = isset($champsDuType[$champ]) && $champsDuType[$champ] ? ['required'] : ['nullable'];
			}
		} else {
			$rules += [
				'mention_bac' => ['required', 'string'],
				'serie' => ['required', 'string'],
				'numero_table' => ['required', 'digits_between:1,7'],
				'annee_bac' => ['required', 'integer', 'min:1990', 'max:' . date('Y')],
				'etablissement_diplome' => ['nullable', 'string'],
			];
		}

		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, [
			'numero_table.digits_between' => "Le numéro de table doit contenir uniquement des chiffres (7 maximum).",
		]);
		if ($validator->fails()) {
			return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
		}

		$candidat->update([
			...$request->only(['numero_table', 'annee_bac', 'mention_bac', 'serie', 'etablissement_diplome']),
			'type_diplome_id' => $typeDiplome?->id,
			'dernier_diplome' => $typeDiplome?->nom,
		]);

		return response()->json(['success' => true]);
	}

	public function soumettreEtape3Documents(Request $request, string $draftToken)
	{
		try {
			$candidat = $this->candidatureParDraftToken($draftToken);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(['success' => false, 'message' => "Dossier introuvable."], 404);
		}

		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
			'niveau_id' => ['required', 'exists:niveaux,id'],
			'filiere_id' => ['nullable', 'exists:filieres,id'],
		]);
		if ($validator->fails()) {
			return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
		}

		$candidat->update($request->only(['niveau_id', 'filiere_id']));

		try {
			$this->updateOrCreateAlbum($request, $candidat);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return response()->json(['success' => false, 'errors' => $e->errors()], 422);
		}

		return response()->json(['success' => true]);
	}

	public function soumettreEtape4Finaliser(Request $request, string $draftToken)
	{
		try {
			$candidat = $this->candidatureParDraftToken($draftToken);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(['success' => false, 'message' => "Dossier introuvable."], 404);
		}

		// Note : on ne réutilise pas reglesChampsConfigurables() ici — cette méthode
		// suppose que TOUTES les données (identité, BAC...) arrivent dans la même
		// requête que les tuteurs, ce qui n'est plus le cas avec l'inscription en
		// plusieurs étapes (les champs BAC ont déjà été validés et sauvegardés à
		// l'étape 2). Seules les règles sur les tuteurs sont pertinentes ici.
		$configs = CandidatureFieldConfig::get()->keyBy('champ_key');
		$champsTuteur = ['nom' => true, 'prenom' => true, 'profession' => false, 'employeur' => false, 'email' => false, 'tel' => false, 'adresse' => false];
		$rules = ['accept_cgu' => ['accepted']];
		foreach ($champsTuteur as $champ => $defaut) {
			$obligatoire = in_array($champ, ['nom', 'prenom'])
				? (bool) ($configs['tuteur_' . $champ]->obligatoire ?? $defaut)
				: (($configs['tuteur_' . $champ] ?? null)?->afficher && $configs['tuteur_' . $champ]->obligatoire);
			$rules["tuteurs.*.$champ"] = [$obligatoire ? 'required' : 'nullable', 'string'];
		}

		$validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
		}

		DB::transaction(function () use ($request, $candidat) {
			$tuteursValides = [];
			foreach ($request->input('tuteurs', []) as $tuteurEntry) {
				if (blank($tuteurEntry['nom'] ?? null) && blank($tuteurEntry['prenom'] ?? null)) {
					continue;
				}

				$donneesTuteur = [
					'nom' => $tuteurEntry['nom'] ?? null,
					'prenom' => $tuteurEntry['prenom'] ?? null,
					'profession' => $tuteurEntry['profession'] ?? null,
					'employeur' => $tuteurEntry['employeur'] ?? null,
					'email' => $tuteurEntry['email'] ?? null,
					'tel' => $tuteurEntry['tel'] ?? null,
					'adresse' => $tuteurEntry['adresse'] ?? null,
					'responsable_des_frais' => filter_var($tuteurEntry['responsable_des_frais'] ?? false, FILTER_VALIDATE_BOOLEAN),
				];

				$candidat->tuteurs()->create($donneesTuteur);
				$tuteursValides[] = $donneesTuteur;
			}

			if (!empty($tuteursValides)) {
				$responsableData = collect($tuteursValides)->firstWhere('responsable_des_frais', true) ?? $tuteursValides[0];
				$candidat->responsable()->create(collect($responsableData)->except('responsable_des_frais')->all());
			}

			// Le mot de passe généré à l'étape 1 n'existe plus qu'en clair à cet instant
			// précis (hashé aussitôt après) : on en régénère un nouveau ici, au moment où
			// le dossier est réellement complet, pour pouvoir l'envoyer par email au candidat.
			$plainPassword = Str::random(8);
			$candidat->update([
				'soumis_le' => now(),
				'password' => Hash::make($plainPassword),
			]);

			$message = $candidat->greeting(true) . ", votre dossier de candidature a été déposé avec succès.";
			$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message, $plainPassword));
			$this->notifierResponsablesNouvelleCandidature($candidat);
		});

		Auth::guard('web_candidatures')->login($candidat);

		return response()->json([
			'success' => true,
			'message' => 'Le dossier a été déposé avec succès.',
			'candidat' => $candidat,
		], 201);
	}

	public function updateByAdmin(Request $request, Candidature $candidature)
	{
		$request->validate([
			'nom' => ['required', 'string', 'max:255'],
			'prenom' => ['required', 'string', 'max:255'],
			'genre' => ['required', 'string'],
			'date_naissance' => ['required', 'date'],
			'lieu_naissance' => ['required', 'string', 'max:255'],
			'nationalite' => ['required', 'string'],
			'tel' => ['required', 'string'],
			'niveau_id' => ['required', 'exists:niveaux,id'],
			'filiere_id' => ['nullable', 'exists:filieres,id'],
			'email' => [
				'required',
				'email',
				'max:255',
				'unique:candidatures,email,' . $candidature->id
			],
			...$this->reglesChampsConfigurables($request, $candidature),
		], [
			'annee_bac.max' => "L'année du BAC ne peut pas être postérieure à l'année en cours (" . date('Y') . ").",
			'numero_table.digits_between' => "Le numéro de table doit contenir uniquement des chiffres (7 maximum).",
		]);

		$typeDiplome = $request->filled('type_diplome_id')
			? \App\Models\TypeDiplome::find($request->input('type_diplome_id'))
			: null;

		$candidature->update([
			...$request->only([
				'nom',
				'prenom',
				'nom_jeune_fille',
				'numero_table',
				'annee_bac',
				'mention_bac',
				'serie',
				'etablissement_diplome',
				'genre',
				'date_naissance',
				'lieu_naissance',
				'email',
				'nationalite',
				'hobbit',
				'tel',
				'tel2',
				'tel3',
				'bp',
				'fax',
				'niveau_id',
				'filiere_id',
				'adresse',
				'numero_bordereau',
				'moyen_connaissance_id',
				'moyen_connaissance_precision',
			]),
			'type_diplome_id' => $typeDiplome?->id,
			'dernier_diplome' => $typeDiplome?->nom ?? $candidature->dernier_diplome,
		]);

		// Tuteurs répétables + responsable des frais — même logique que store()/storeByAdmin() :
		// on remplace la liste existante par celle envoyée (le formulaire renvoie toujours la
		// liste complète, pas un diff).
		if ($request->has('tuteurs')) {
			$candidature->tuteurs()->delete();
			$candidature->responsable()->delete();

			$tuteursValides = [];
			foreach ($request->input('tuteurs', []) as $tuteurEntry) {
				if (blank($tuteurEntry['nom'] ?? null) && blank($tuteurEntry['prenom'] ?? null)) {
					continue;
				}

				$donneesTuteur = [
					'nom' => $tuteurEntry['nom'] ?? null,
					'prenom' => $tuteurEntry['prenom'] ?? null,
					'profession' => $tuteurEntry['profession'] ?? null,
					'employeur' => $tuteurEntry['employeur'] ?? null,
					'email' => $tuteurEntry['email'] ?? null,
					'tel' => $tuteurEntry['tel'] ?? null,
					'adresse' => $tuteurEntry['adresse'] ?? null,
					'responsable_des_frais' => filter_var($tuteurEntry['responsable_des_frais'] ?? false, FILTER_VALIDATE_BOOLEAN),
				];

				$candidature->tuteurs()->create($donneesTuteur);
				$tuteursValides[] = $donneesTuteur;
			}

			if (!empty($tuteursValides)) {
				$responsableData = collect($tuteursValides)->firstWhere('responsable_des_frais', true) ?? $tuteursValides[0];
				$candidature->responsable()->create(collect($responsableData)->except('responsable_des_frais')->all());
			}
		}

		// Update or Create Album (documents dynamiques + type_diplome, méthode déjà
		// partagée avec store()/storeByAdmin()).
		$this->updateOrCreateAlbum($request, $candidature);

		return response()->json([
			'success' => true,
			'message' => 'Le dossier a été mis à jour avec succès.',
			'candidat' => $candidature
		]);
	}

	private function updateOrCreateAlbum(Request $request, Candidature $candidat)
	{
		$requirements = \App\Models\DocumentRequirement::with('documentType')
            ->where('niveau_id', $candidat->niveau_id)
			->where(function ($q) use ($candidat) {
				$q->whereNull('filiere_id')->orWhere('filiere_id', $candidat->filiere_id);
			})->get();

		$filePrefix = Str::slug($candidat->nom . '_' . $candidat->prenom);

		$mapKeyForUpload = function ($dbKey) {
			$map = [
				'lettre' => 'lettre_file',
				'naissance' => 'naissance_file',
				'diplome' => 'diplome_file',
				'nationalite' => 'nationalite_file',
				'photo' => 'photo_identite_file',
				'certificat_medical' => 'certificat_medical_file',
				'cv_path' => 'cv_file',
				'cv' => 'cv_file',
				'coupon' => 'coupon_file',
				'releve_bac1_path' => 'releve_bac1',
				'releve_bac2_path' => 'releve_bac2',
			];
			return $map[$dbKey] ?? $dbKey;
		};

        // 1. Validation dynamique des formats et obligations
        $rules = [];
        $messages = [];

        foreach ($requirements as $req) {
            $type = $req->documentType;
            if (!$type) continue;
            
            $docKey = $type->document_key;
            $requestKey = $mapKeyForUpload($docKey);
            
            // Déterminer la clé utilisée dans la requête
            $actualKey = $docKey;
            if ($request->hasFile($requestKey)) {
                $actualKey = $requestKey;
            } elseif ($request->hasFile($docKey . '_file')) {
                $actualKey = $docKey . '_file';
            }

            // Vérifier si le document a déjà été uploadé (pour les mises à jour)
            $isAlreadyUploaded = $candidat->submittedDocuments()->where('document_key', $docKey)->exists();

            $ruleSet = [];
            
            if ($req->is_obligatoire && !$isAlreadyUploaded) {
                $ruleSet[] = 'required';
                $messages["{$actualKey}.required"] = "Le document {$type->nom_affichage} est obligatoire.";
            } else {
                $ruleSet[] = 'nullable';
            }

            // Limite stricte de 5 Mo (5120 Ko)
            $ruleSet[] = 'max:5120';
            $messages["{$actualKey}.max"] = "Le document {$type->nom_affichage} ne doit pas dépasser 5 Mo.";

            if ($type->accepted_formats === 'image') {
                $ruleSet[] = 'image';
                $ruleSet[] = 'mimes:jpeg,png,jpg,gif,webp';
                $messages["{$actualKey}.image"] = "Le document {$type->nom_affichage} doit être une image.";
                $messages["{$actualKey}.mimes"] = "Le document {$type->nom_affichage} doit être au format jpeg, png, jpg, gif ou webp.";
            } elseif ($type->accepted_formats === 'pdf') {
                $ruleSet[] = 'mimes:pdf';
                $messages["{$actualKey}.mimes"] = "Le document {$type->nom_affichage} doit être un fichier PDF.";
            }

            if ($type->is_multiple) {
                if ($req->is_obligatoire && !$isAlreadyUploaded) {
                    $rules[$actualKey] = ['required', 'array', 'min:1'];
                    $messages["{$actualKey}.required"] = "Le document {$type->nom_affichage} est obligatoire.";
                } else {
                    $rules[$actualKey] = ['nullable', 'array'];
                }
                
                $childRules = array_filter($ruleSet, fn($r) => $r !== 'required' && $r !== 'nullable');
                $rules["{$actualKey}.*"] = $childRules;
                
                $messages["{$actualKey}.*.max"] = "Chaque fichier de {$type->nom_affichage} ne doit pas dépasser 5 Mo.";
                if ($type->accepted_formats === 'image') {
                    $messages["{$actualKey}.*.image"] = "Chaque fichier de {$type->nom_affichage} doit être une image.";
                    $messages["{$actualKey}.*.mimes"] = "Chaque fichier de {$type->nom_affichage} doit être au format jpeg, png, jpg, gif ou webp.";
                } elseif ($type->accepted_formats === 'pdf') {
                    $messages["{$actualKey}.*.mimes"] = "Chaque fichier de {$type->nom_affichage} doit être un fichier PDF.";
                }
            } else {
                $rules[$actualKey] = $ruleSet;
            }
        }

        if (!empty($rules)) {
            \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages)->validate();
        }

		foreach ($requirements as $req) {
			$docKey = $req->documentType->document_key ?? null;
			if (!$docKey) continue;

			$requestKey = $mapKeyForUpload($docKey);
			$folder = 'documents/' . $docKey;

			$actualKey = null;
			if ($request->hasFile($requestKey)) {
				$actualKey = $requestKey;
			} elseif ($request->hasFile($docKey)) {
				$actualKey = $docKey;
			} elseif ($request->hasFile($docKey . '_file')) {
				$actualKey = $docKey . '_file';
			}

			if ($actualKey) {
				$isMultiple = in_array($actualKey, ['releve_bac1', 'releve_bac2']) || str_contains($actualKey, 'bulletins') || ($req->documentType->is_multiple ?? false);

				if ($isMultiple) {
					$paths = $this->storeMultipleFiles($request, $actualKey, $folder, 'files', $filePrefix);
					if (!empty($paths)) {
						$candidat->submittedDocuments()->updateOrCreate(
							['document_key' => $docKey],
							['file_path' => json_encode($paths), 'statut' => 'en_attente']
						);
					}
				} else {
					$path = $this->storeFile($request, $actualKey, $folder, $filePrefix);
					$candidat->submittedDocuments()->updateOrCreate(
						['document_key' => $docKey],
						['file_path' => $path, 'statut' => 'en_attente']
					);
				}
			}
		}

		// Type diplome
		if ($request->has('type_diplome')) {
			$data['type_diplome'] = $request->get('type_diplome');
			if ($candidat->album) {
				$candidat->album->update($data);
			} else {
				$candidat->album()->create($data);
			}
		}
	}



	public function payementCandidaturesStore(Request $request): JsonResponse
	{
		if (!$request->input('paye')) {
			return response()->json([
				'success' => false,
				'message' => 'La liste d\'étudiants ne peut être vide.'
			], 422);
		}

		CandidatureFraisPayementJob::dispatch($request->collect('paye'));

		return response()->json([
			'success' => true,
			'message' => 'Liste de paiements soumise pour traitement.'
		]);
	}

	public function presenceControlStore(Request $request): Response
	{
		try {
			app(\App\Services\CandidaturePresenceService::class)->processPresence($request->collect('candidats'));
		} catch (Throwable $throwable) {
			return __500($throwable->getMessage());
		}
		return __200('Liste soumise avec succès');
	}

	public function storeGroupClassAssignment(Request $request)
	{
		$request->validate([
			'group_id' => ['required', 'exists:groups,id'],
			'candidats' => ['required', 'array', 'min:1'],
			'candidats.*' => ['string'],
		]);

		$group = Group::findOrFail($request->input('group_id'));

		$candidatures = Candidature::query()
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->where('frais_paye', true)
			->where('participation', true)
			->where('admission', true)
			->whereNull('motif')
			->whereIn('slug', $request->input('candidats'))
			->get();

		$successCount = 0;
		foreach ($candidatures as $candidature) {
			$candidature->update([
				'niveau_id' => $group->niveau_id,
				'filiere_id' => $group->filiere_id,
				'group_id' => $group->id, // On sauvegarde juste le choix du groupe ici
			]);
			$successCount++;
		}

		return response()->json([
			'success' => true,
			'message' => 'Groupe pré-attribué avec succès pour ' . $successCount . ' candidat(s). L\'inscription finale génèrera la scolarité.'
		]);
	}

	public function admissionControl(Request $request): JsonResponse
	{
		ConcoursResultJob::dispatch($request->str('admis'), $request->str('recales'));
		return response()->json([
			'success' => true,
			'message' => 'Décisions appliquées avec succès. L\'envoi des messages est en cours.'
		]);
	}

	public function insertStudent(Request $request, Candidature $candidature)
	{
		if (!$candidature) {
			return response()->json([
				'success' => false,
				'message' => "Candidature introuvable"
			], 404);
		}

		$anneeId = $request->input('annee_scolaire_id');
		$activeAnnee = $anneeId ? \App\Models\AnneeScolaire::find($anneeId) : \App\Models\AnneeScolaire::where('active', true)->first();
		if (!$activeAnnee) {
			return response()->json([
				'success' => false,
				'message' => "Aucune année scolaire active configurée"
			], 422);
		}

		return DB::transaction(function () use ($request, $candidature, $activeAnnee) {
			// 1. Gestion du groupe
			$groupId = $request->input('group_id');
			if (!$groupId) {
				$availableGroups = FiliereGroup::where('filiere_id', $candidature->filiere_id)
					->join('groups', 'filiere_group.group_id', '=', 'groups.id')
					->where('groups.niveau_id', $candidature->niveau_id)
					->select('groups.*')
					->get();

				if ($availableGroups->count() === 1) {
					$groupId = $availableGroups->first()->id;
				} else if ($availableGroups->count() > 1) {
					throw new Exception("Plusieurs groupes disponibles. Veuillez en sélectionner un.");
				} else {
					throw new Exception("Aucun groupe disponible pour cette filière et ce niveau.");
				}
			}

			// 2. Préparation Étudiant
			$year = $activeAnnee->date_debut ? \Carbon\Carbon::parse($activeAnnee->date_debut)->year : today()->year;
			$nextYear = $year + 1;
			$promotion = "{$year}-{$nextYear}";

			if ($candidature->etudiant_id) {
				$etudiant = Etudiant::findOrFail($candidature->etudiant_id);
				$etudiant->update([
					'advertiser_id' => $request->input('advertiser_id', $candidature->advertiser_id),
					'promotion' => $promotion,
					'annee_admission' => $year,
				]);
			} else {
				$emailPro = $request->filled('email_pro') ? $request->input('email_pro') : $candidature->email;
				$passwordHash = $request->filled('password') ? bcrypt($request->input('password')) : $candidature->password;

				$etudiant = Etudiant::create([
					'nom' => $candidature->nom,
					'nom_jeune_fille' => $candidature->nom_jeune_fille,
					'prenom' => $candidature->prenom,
					'genre' => $candidature->genre,
					'date_naissance' => $candidature->date_naissance,
					'lieu_naissance' => $candidature->lieu_naissance,
					'nationalite' => $candidature->nationalite,
					'tel' => $candidature->tel,
					'email' => $emailPro,
					'password' => $passwordHash,
					'image' => config('images.etudiants.woman'),
					'annee_admission' => $year,
					'promotion' => $promotion,
					'advertiser_id' => $request->input('advertiser_id', $candidature->advertiser_id),
					'matricule' => $request->input('matricule') ?: Etudiant::generateNextMatricule($year),
				]);

				$roleEtudiant = Role::where('nom', 'Etudiant')->first();
				if ($roleEtudiant) {
					$etudiant->roles()->attach($roleEtudiant->id);
				}
			}

			// 3. Affectation Groupe
			$modeFormation = $request->input('mode_formation', 'Présentiel');
			$etudiant->groups()->syncWithoutDetaching([
				$groupId => [
					"annee_scolaire_id" => $activeAnnee->id,
					"niveau_id" => $candidature->niveau_id,
					"filiere_id" => $candidature->filiere_id,
					"mode_formation" => $modeFormation,
				]
			]);

			// 4. Gestion de la Bourse
			$bourseId = $request->input('bourse_id');
			$bourseEtudiant = null;
			if ($bourseId) {
				$bourseEtudiant = BourseEtudiant::updateOrCreate(
					[
						'etudiant_id' => $etudiant->id,
						'annee_scolaire_id' => $activeAnnee->id
					],
					[
						'bourse_id' => $bourseId,
						'slug' => Str::uuid()
					]
				);
			}

			// 5. Gestion Financière (Frais d'Inscription)
			if ($request->boolean('frais_inscription_paye')) {
				$fraisInsc = FraisInscription::where('annee_scolaire_id', $activeAnnee->id)
					->where('active', true)
					->first() ?: FraisInscription::where('annee_scolaire_id', $activeAnnee->id)->latest()->first();

				if ($fraisInsc) {
					Paiement::updateOrCreate(
						[
							"etudiant_id" => $etudiant->id,
							"payable_id" => $fraisInsc->id,
							"payable_type" => FraisInscription::class,
						],
						[
							"montant" => $fraisInsc->montant,
							"nature_paiement" => "inscription",
							"mode_paiement" => $request->input('mode_paiement', 'espece'),
							"frais_retrait_mm" => $request->input('frais_retrait', 0),
							"reference" => "REG-" . strtoupper(Str::random(8)),
							"status" => "valide",
							"date_paiement" => now(),
						]
					);
				}
			}

			// 6. Génération de la Scolarité et des Tranches
			$fraisScolarite = FraisScolarite::getFraisForEtudiant(
				$candidature->niveau_id,
				$candidature->genre,
				$candidature->filiere_id,
				$activeAnnee->id,
				$modeFormation
			);

			if ($fraisScolarite) {
				$montantInitial = (float) $fraisScolarite->montant;
				$montantApresBourse = $montantInitial;

				if ($bourseEtudiant && $bourseEtudiant->bourse) {
					$bourse = $bourseEtudiant->bourse;
					if ($bourse->type === 'pourcentage') {
						$montantApresBourse = round($montantInitial * (100 - $bourse->valeur) / 100);
					} else {
						$montantApresBourse = max(0, $montantInitial - $bourse->valeur);
					}
				}

				$fEtudiant = FraisEtudiant::updateOrCreate(
					[
						'etudiant_id' => $etudiant->id,
						'annee_scolaire_id' => $activeAnnee->id
					],
					[
						'frais_scolarite_id' => $fraisScolarite->id,
						'montant_initial' => $montantInitial,
						'montant_apres_bourse' => $montantApresBourse,
						'bourse_etudiant_id' => $bourseEtudiant ? $bourseEtudiant->id : null,
						'type_paiement' => 'tranches_globales',
						'frequence_paiement' => 'annuel',
						'statut' => 'en_cours',
						'slug' => Str::uuid()
					]
				);

				// Supprimer les anciennes tranches pour régénérer proprement si nécessaire
				$fEtudiant->echeances()->delete();
				$fEtudiant->creerEcheancesDepuisTranchesGlobales();
			}

			// 7. Transfert des données polymorphiques
			$updatedData = [
				'owner_id' => $etudiant->id,
				'owner_type' => Etudiant::class,
			];
			if ($candidature->album)
				$candidature->album->update($updatedData);
			$candidature->submittedDocuments()->update($updatedData);
			if ($candidature->responsable)
				$candidature->responsable->update($updatedData);
			if ($candidature->tuteur)
				$candidature->tuteur->update($updatedData);

			// 8. Mise à jour finalisation candidature
			$candidature->update([
				'etudiant_id' => $etudiant->id,
				'acceptation_date' => now(),
				'end_accessibility_date' => now()->addDays(3)
			]);

			$emailProToSend = $request->input('email_pro', $etudiant->email);
			$passwordToSend = $request->input('password', 'Votre ancien mot de passe de candidature');

			$etudiant->notify(new CandidatToEtudiantWelcomeNotification($etudiant->greeting(), $emailProToSend, $passwordToSend));

			return response()->json([
				'success' => true,
				'message' => 'L\'étudiant a été inscrit, son compte créé et son échéancier financier généré avec succès.'
			]);
		});
	}


	public function getNiveaux()
	{
		return Niveau::query()->orderBy('libelle')->get();
	}

	public function getFilieres()
	{
		return Filiere::query()->orderBy('nom')->get();
	}

	public function reorienter(Request $request, Candidature $candidature)
	{
		// Décision finale de l'académie : seulement une fois le dossier transmis par le
		// chargé de la clientèle.
		if (!$candidature->transmis_academie) {
			return response()->json([
				'success' => false,
				'message' => "Ce dossier doit d'abord être transmis à l'académie avant de pouvoir être réorienté."
			], 422);
		}

		if ($refus = $this->refuserSiPasAcademie()) {
			return $refus;
		}

		$existe = Reorientation::where('candidature_id', $candidature->id)
			->where('annee_scolaire_id', $candidature->annee_scolaire_id)
			->exists();

		if ($existe) {
			return response()->json([
				'message' => 'Une réorientation existe déjà pour cette année scolaire.'
			], 409);
		}

		Reorientation::create([
			'candidature_id' => $candidature->id,
			'filiere_id' => $candidature->filiere_id,
			'niveau_id' => $candidature->niveau_id,
			'motif' => $request->motif,
			'annee_scolaire_id' => $candidature->annee_scolaire_id
		]);

		$candidature->update([
			'filiere_id' => $request->filiere_id,
			'niveau_id' => $request->niveau_id
		]);

		$candidature->load(['filiere', 'niveau']);

		$message = $candidature->greeting(true) . ". Nous vous informons que votre dossier de candidature a été réorienté vers la filière " . $candidature->filiere->nom . " (Niveau: " . $candidature->niveau->libelle . ") pour le motif suivant : " . $request->motif;
		$candidature->notify(new \App\Notifications\Candidatures\CandidatReorientationNotification($message, $request->motif, $candidature->filiere->nom, $candidature->niveau->libelle));

		// Notifier le chargé de clientèle / administration de la réorientation
		$chargeClientele = User::whereHas('roles.permissions', function ($q) {
			$q->whereIn('slug', ['transmettre-candidature', 'recevoir-notification-nouvelle-candidature']);
		})->get();

		if ($chargeClientele->count() > 0) {
			$userActor = auth('sanctum')->user() ?? auth()->user();
			Notification::send(
				$chargeClientele,
				new \App\Notifications\Candidatures\CandidatureStatusUpdatedNotification(
					$candidature,
					'Candidature Réorientée par l\'Académie',
					'Le dossier a été réorienté vers : ' . $candidature->filiere->nom . ' (' . $candidature->niveau->libelle . '). Motif : ' . $request->motif,
					$userActor ? $userActor->nom . ' ' . $userActor->prenom : null
				)
			);
		}

		return response()->json([
			'message' => 'Réorientation effectuée avec succès.'
		], 201);
	}

	/**
	 * Notifie par e-mail et notification système les agents (par défaut Chargé de clientèle et Informaticien)
	 * possédant la permission 'recevoir-notification-nouvelle-candidature' lors du dépôt d'une candidature.
	 */
	private function notifierResponsablesNouvelleCandidature(Candidature $candidat): void
	{
		try {
			$responsables = User::whereHas('roles.permissions', function ($q) {
				$q->where('slug', 'recevoir-notification-nouvelle-candidature');
			})->get();

			if ($responsables->count() > 0) {
				Notification::send($responsables, new NewCandidatureSubmittedNotification($candidat));
			}
		} catch (\Throwable $e) {
			\Illuminate\Support\Facades\Log::error("Erreur lors de l'envoi des notifications de candidature : " . $e->getMessage());
		}
	}
}
