<?php

namespace App\Http\Controllers;

use App\Enums\TypeDiplomeEnum;
use App\Http\Requests\Candidature\StoreRequest;
use App\Http\Resources\CandidatureResource;
use App\Notifications\Candidatures\CandidatAbsentNotification;
use App\Notifications\Candidatures\CandidatPresentNotification;
use App\Jobs\{CandidatureFraisPayementJob, ConcoursResultJob, SmsSendingProcess};
use App\Models\{AnneeScolaire, Bourse, BourseEtudiant, Candidature, CandidatureDocument, Echeance, Etudiant, FiliereGroup, FraisEtudiant, FraisInscription, FraisScolarite, Group, Inscription, Paiement, Reorientation, Role, TranchePaiement};
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
		$candidature->load(['documents', 'album', 'tuteur', 'responsable', 'niveau', 'filiere', 'advertiser']);

		if (request()->ajax() || request()->wantsJson()) {
			return response()->json([
				'data' => $candidature
			]);
		}

		return view('admin.candidatures.show', compact('candidature'))->with([
			'album' => $candidature->album,
			'niveau' => $candidature->niveau,
			'filiere' => $candidature->filiere,
			'filieres' => Filiere::all(),
			'niveaux' => Niveau::all(),
		]);
	}

	public function create(): View
	{
		return view('candidatures.create')->with([
			'candidature' => new Candidature(),
			'niveaux' => $this->getNiveaux(),
			'filieres' => $this->getFilieres(),
			'countries' => CountryListFacade::getList(config('app.locale'))
		]);
	}

	public function store(Request $request)
	{
		$anneeScolaireId = getAnneeScolaireId();

		// 1. Vérification d'unicité de candidature par année scolaire
		$exists = Candidature::where('email', $request->email)
			->where('annee_scolaire_id', $anneeScolaireId)
			->exists();

		if ($exists) {
			return response()->json([
				'success' => false,
				'message' => "Vous avez déjà déposé une candidature pour cette année scolaire."
			], 422);
		}

		// 2. Vérification si l'étudiant est déjà inscrit (optionnel mais recommandé)
		if (Etudiant::where('email', $request->email)->exists()) {
			return response()->json([
				'success' => false,
				'message' => "Un compte étudiant existe déjà avec cet email. Veuillez passer par la procédure de réinscription."
			], 422);
		}

		// 3. Création du candidat
		$candidat = Candidature::create([
			...$request->only([
				'nom',
				'prenom',
				'nom_jeune_fille',
				'numero_table',
				'annee_bac',
				'serie',
				'lettre_motivation',
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
			]),
			...injectAnneeScolaireId(),
			'password' => Hash::make('password'),
			'code' => fake()->unique()->numberBetween(9999, 100000)
		]);

		// 2. Création du responsable
		if ($request->filled('nom_resp')) {
			$candidat->responsable()->create([
				'nom' => $request->get('nom_resp'),
				'prenom' => $request->get('prenom_resp'),
				'profession' => $request->get('profession_resp'),
				'employeur' => $request->get('employeur_resp'),
				'email' => $request->get('email_resp'),
				'tel' => $request->get('tel_resp'),
				'adresse' => $request->get('adresse_resp'),
				'fax' => $request->get('fax_resp'),
				'bp' => $request->get('bp_resp'),
			]);
		}

		// 3. Création du tuteur
		if ($request->filled('nom_tuteur')) {
			$candidat->tuteur()->create([
				'nom' => $request->get('nom_tuteur'),
				'prenom' => $request->get('prenom_tuteur'),
				'profession' => $request->get('profession_tuteur'),
				'employeur' => $request->get('employeur_tuteur'),
				'email' => $request->get('email_tuteur'),
				'tel' => $request->get('tel_tuteur'),
				'adresse' => $request->get('adresse_tuteur'),
				'fax' => $request->get('fax_tuteur'),
				'bp' => $request->get('bp_tuteur'),
				'candidature_id' => $candidat->getAttribute('id')
			]);
		}

		// 4. Création de l'album (utilise la version corrigée ci-dessus)
		$this->createAlbum($request, $candidat);

		// 5. Connexion et notification
		Auth::guard('web_candidatures')->login($candidat);

		$message = $candidat->greeting(true);
		$message .= ", votre dossier de candidature a été déposé avec succès.";

		$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message));
		
		return response()->json([
			'success' => true,
			'message' => 'Le dossier a été déposé avec succès.',
			'candidat' => $candidat
		], 201);
	}
	public function storeByAdmin(Request $request)
	{
		$request->validate([
			'email' => [
				'nullable',
				'email',
				'max:255',
				'unique:candidatures,email'
			],
			'email_resp' => [
				'nullable',
				'email',
				'max:255',
			],
			'email_tuteur' => [
				'nullable',
				'email',
				'max:255',
			],
		]);

		$candidat = Candidature::create([
			...$request->only([
				'nom', 'prenom', 'nom_jeune_fille', 'numero_table', 'annee_bac', 'serie',
				'lettre_motivation', 'genre', 'date_naissance', 'lieu_naissance', 'email',
				'nationalite', 'hobbit', 'tel', 'tel2', 'tel3', 'bp', 'fax', 'niveau_id', 'filiere_id',
			]),
			...injectAnneeScolaireId(),
			'password' => Hash::make('password'),
			'code' => fake()->unique()->numberBetween(9999, 100000)
		]);

		// 2. Création du responsable
		if ($request->filled('nom_resp')) {
			$candidat->responsable()->create([
				'nom' => $request->get('nom_resp'),
				'prenom' => $request->get('prenom_resp'),
				'profession' => $request->get('profession_resp'),
				'employeur' => $request->get('employeur_resp'),
				'email' => $request->get('email_resp'),
				'tel' => $request->get('tel_resp'),
				'adresse' => $request->get('adresse_resp'),
				'fax' => $request->get('fax_resp'),
				'bp' => $request->get('bp_resp'),
			]);
		}

		// 3. Création du tuteur
		if ($request->filled('nom_tuteur')) {
			$candidat->tuteur()->create([
				'nom' => $request->get('nom_tuteur'),
				'prenom' => $request->get('prenom_tuteur'),
				'profession' => $request->get('profession_tuteur'),
				'employeur' => $request->get('employeur_tuteur'),
				'email' => $request->get('email_tuteur'),
				'tel' => $request->get('tel_tuteur'),
				'adresse' => $request->get('adresse_tuteur'),
				'fax' => $request->get('fax_tuteur'),
				'bp' => $request->get('bp_tuteur'),
				'candidature_id' => $candidat->getAttribute('id')
			]);
		}

		// 4. Création de l'album
		$this->updateOrCreateAlbum($request, $candidat);

		// 5. Connexion et notification
		Auth::guard('web_candidatures')->login($candidat);

		$message = $candidat->greeting(true);
		$message .= ", votre dossier de candidature a été déposé avec succès.";

		$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message));

		return response()->json([
			'success' => true,
			'message' => 'Le dossier a été déposé avec succès par l\'administration.',
			'candidat' => $candidat
		], 201);
	}

	public function updateByAdmin(Request $request, Candidature $candidature)
	{
		$request->validate([
			'email' => [
				'nullable',
				'email',
				'max:255',
				'unique:candidatures,email,' . $candidature->id
			],
			'email_resp' => [
				'nullable',
				'email',
				'max:255',
			],
			'email_tuteur' => [
				'nullable',
				'email',
				'max:255',
			],
		]);

		$candidature->update($request->only([
			'nom', 'prenom', 'nom_jeune_fille', 'numero_table', 'annee_bac', 'serie',
			'lettre_motivation', 'genre', 'date_naissance', 'lieu_naissance', 'email',
			'nationalite', 'hobbit', 'tel', 'tel2', 'tel3', 'bp', 'fax', 'niveau_id', 'filiere_id',
		]));

		// Update or Create Responsable
		if ($request->filled('nom_resp')) {
			$candidature->responsable()->updateOrCreate(
				[],
				[
					'nom' => $request->get('nom_resp'),
					'prenom' => $request->get('prenom_resp'),
					'profession' => $request->get('profession_resp'),
					'employeur' => $request->get('employeur_resp'),
					'email' => $request->get('email_resp'),
					'tel' => $request->get('tel_resp'),
					'adresse' => $request->get('adresse_resp'),
					'fax' => $request->get('fax_resp'),
					'bp' => $request->get('bp_resp'),
				]
			);
		}

		// Update or Create Tuteur
		if ($request->filled('nom_tuteur')) {
			$candidature->tuteur()->updateOrCreate(
				[],
				[
					'nom' => $request->get('nom_tuteur'),
					'prenom' => $request->get('prenom_tuteur'),
					'profession' => $request->get('profession_tuteur'),
					'employeur' => $request->get('employeur_tuteur'),
					'email' => $request->get('email_tuteur'),
					'tel' => $request->get('tel_tuteur'),
					'adresse' => $request->get('adresse_tuteur'),
					'fax' => $request->get('fax_tuteur'),
					'bp' => $request->get('bp_tuteur'),
				]
			);
		}

		// Update or Create Album
		$this->updateOrCreateAlbum($request, $candidature);

		return response()->json([
			'success' => true,
			'message' => 'Le dossier a été mis à jour avec succès.',
			'candidat' => $candidature
		]);
	}

	private function updateOrCreateAlbum(Request $request, Candidature $candidat)
	{
		$filePrefix = Str::slug($candidat->nom . '_' . $candidat->prenom);
		$album = $candidat->album;

		$data = [];

		// Files unique
		$fileFields = [
			'lettre_file' => 'lettre',
			'naissance_file' => 'naissance',
			'diplome_file' => 'diplome',
			'nationalite_file' => 'nationalite',
			'photo_identite_file' => 'photo',
			'certificat_medical_file' => 'certificat_medical',
			'coupon_file' => 'coupon',
			'cv_file' => 'cv_path'
		];

		foreach ($fileFields as $requestKey => $dbKey) {
			if ($request->hasFile($requestKey)) {
				// Store new file
				$folder = match ($requestKey) {
					'lettre_file' => static::LETTRE,
					'naissance_file' => static::NAISSANCE,
					'diplome_file' => static::DIPLOME,
					'nationalite_file' => static::NATIONALITE,
					'photo_identite_file' => static::PHOTO_IDENTITE,
					'certificat_medical_file' => static::CERTIFICAT_MEDICAL,
					'coupon_file' => static::COUPON,
					'cv_file' => 'cv',
					default => 'others'
				};
				$data[$dbKey] = $this->storeFile($request, $requestKey, $folder, $filePrefix);
			}
		}

		// Bulletins
		$allBulletins = [];
		$hasBulletins = false;
		foreach (['seconde', 'premiere', 'terminale'] as $niveau) {
			if ($request->hasFile("bulletins_{$niveau}")) {
				$paths = $this->storeMultipleFiles($request, "bulletins_{$niveau}", 'bulletins', $niveau, $filePrefix);
				$allBulletins[$niveau] = $paths;
				$hasBulletins = true;
			}
		}
		if ($hasBulletins) {
			$data['bulletins_lycee_paths'] = json_encode($allBulletins);
		}

		// Relevés
		if ($request->hasFile("releve_bac1")) {
			$bac1Paths = $this->storeMultipleFiles($request, "releve_bac1", 'releves', 'bac1', $filePrefix);
			if (!empty($bac1Paths)) $data['releve_bac1_path'] = $bac1Paths[0];
		}
		if ($request->hasFile("releve_bac2")) {
			$bac2Paths = $this->storeMultipleFiles($request, "releve_bac2", 'releves', 'bac2', $filePrefix);
			if (!empty($bac2Paths)) $data['releve_bac2_path'] = $bac2Paths[0];
		}

		// Type diplome
		if ($request->has('type_diplome')) {
			$data['type_diplome'] = $request->get('type_diplome');
		}

		if ($album) {
			$album->update($data);
		} else {
			$candidat->album()->create($data);
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
			$absentesCandidats = $request->collect('candidats');
			$absentCandidats = Candidature::query()->whereIn('slug', $absentesCandidats)->get();

			$absentCandidats->map(function (Candidature $candidat) {
				$candidat->update([
					'participation' => false,
					'participation_date' => now()
				]);

				$message = $candidat->greeting();
				$message .= '. Nous avons remarqué que vous n\'avez pas pu participer à l\'épreuve du concours  qui a eu
			 lieu . Nous regrettons sincèrement votre absence et comprenons que des imprévus peuvent survenir. 
			 Nous espérons vous voir participer à nos futurs événements et concours';

				$candidat->notify(new CandidatAbsentNotification($message));
				// dump($candidat->notifications()->get());
				// TODO effectuer la tâche sans le dump
			});

			Candidature::query()
				->where('dossier_valide', true)
				->where('frais_paye', true)
				->where('participation', false)
				->whereNull('participation_date')
				->where('admission', false)
				->whereNull('motif')
				->whereNotIn('slug', $absentesCandidats)
				->get()
				->map(function (Candidature $candidat) {
					$message = $candidat->greeting();

					$candidat->update([
						'participation' => true,
						'participation_date' => now()
					]);

					$message .= ' . Nous tenons à vous remercier pour votre participation au concours . Nous tenons à vous informer que 
				les résultats seront annoncés le [Date prévue]. Nous vous prions de bien vouloir patienter jusqu\'à cette date.';

					$candidat->notify(new CandidatPresentNotification($message));
				});
		} catch (Throwable $throwable) {
			return __500($throwable->getMessage());
		}
		return __200('Liste soumise avec succès');
	}

	public function storeGroupClassAssignment(Request $request)
	{
		$request->dd();
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

		$activeAnnee = AnneeScolaire::where('active', true)->first();
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
				$etudiant = Etudiant::create([
					'nom' => $candidature->nom,
					'nom_jeune_fille' => $candidature->nom_jeune_fille,
					'prenom' => $candidature->prenom,
					'genre' => $candidature->genre,
					'date_naissance' => $candidature->date_naissance,
					'lieu_naissance' => $candidature->lieu_naissance,
					'nationalite' => $candidature->nationalite,
					'tel' => $candidature->tel,
					'email' => $candidature->email,
					'password' => $candidature->password,
					'image' => config('images.etudiants.woman'),
					'annee_admission' => $year,
					'promotion' => $promotion,
					'advertiser_id' => $request->input('advertiser_id', $candidature->advertiser_id),
					'matricule' => Str::upper($year . '_' . fake()->unique()->randomNumber(6, true)),
				]);

				$roleEtudiant = Role::where('nom', 'Etudiant')->first();
				if ($roleEtudiant) {
					$etudiant->roles()->attach($roleEtudiant->id);
				}
			}

			// 3. Affectation Groupe
			$etudiant->groups()->syncWithoutDetaching([
				$groupId => [
					"annee_scolaire_id" => $activeAnnee->id,
					"niveau_id" => $candidature->niveau_id,
					"filiere_id" => $candidature->filiere_id
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
				$activeAnnee->id
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
			if ($candidature->album) $candidature->album->update($updatedData);
			if ($candidature->responsable) $candidature->responsable->update($updatedData);
			if ($candidature->tuteur) $candidature->tuteur->update($updatedData);

			// 8. Mise à jour finalisation candidature
			$candidature->update([
				'etudiant_id' => $etudiant->id,
				'acceptation_date' => now(),
				'end_accessibility_date' => now()->addDays(3)
			]);

			$etudiant->notify(new CandidatToEtudiantWelcomeNotification($etudiant->greeting()));

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

		return response()->json([
			'message' => 'Réorientation effectuée avec succès.'
		], 201);
	}
}
