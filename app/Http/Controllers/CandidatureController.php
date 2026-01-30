<?php

namespace App\Http\Controllers;

use App\Enums\TypeDiplomeEnum;
use App\Http\Requests\Candidature\StoreRequest;
use App\Http\Resources\CandidatureResource;
use App\Notifications\Candidatures\CandidatAbsentNotification;
use App\Notifications\Candidatures\CandidatPresentNotification;
use App\Jobs\{CandidatureFraisPayementJob, ConcoursResultJob, SmsSendingProcess};
use App\Models\{AnneeScolaire, Candidature, CandidatureDocument, Etudiant, FiliereGroup, FraisInscription, Group, Inscription, Paiement, Reorientation};
use App\Notifications\Candidatures\CandidatWelcomeNotification;
use App\Traits\ActionsTraits\{IndexTrait, CandidatureFirstValidationTrait};
use App\Traits\FileManagementTrait;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Notifications\Candidatures\CandidatAccountLockNotification;
use App\Notifications\Candidatures\CandidatToEtudiantWelcomeNotification;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, Hash};
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
		$candidature->load(['documents', 'album']);

		// return new  CandidatureResource($candidature);

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

	public function store(Request $request): RedirectResponse
	{
		// 1. Création du candidat
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

		// 3. Création du tuteur
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

		// 4. Création de l'album (utilise la version corrigée ci-dessus)
		$this->createAlbum($request, $candidat);

		// 5. Connexion et notification
		Auth::guard('web_candidatures')->login($candidat);

		$message = $candidat->greeting(true);
		$message .= ", votre dossier de candidature a été déposé avec succès.";

		$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message));

		return to_route('officiel.my-space.show')->with(successMsg('Le dossier a été déposé avec succès.'));
	}
	public function storeByAdmin(Request $request): RedirectResponse
	{

		$request->validate([

			'email' => [
				'required',
				'email',
				'max:255',
				'unique:candidatures,email'
			],
			'email_resp' => [
				'nullable',
				'email',
				'max:255',
				'unique:responsable_frais,email'
			],
			'email_tuteur' => [
				'nullable',
				'email',
				'max:255',
				'unique:tuteurs,email'
			],
		]);


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

		// 3. Création du tuteur
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

		// 4. Création de l'album (utilise la version corrigée ci-dessus)
		$this->createAlbum($request, $candidat);

		// 5. Connexion et notification
		Auth::guard('web_candidatures')->login($candidat);

		$message = $candidat->greeting(true);
		$message .= ", votre dossier de candidature a été déposé avec succès.";

		$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message));

		return to_route('admin.candidatures.index')->with(successMsg('Le dossier a été déposé avec succès.'));
	}

	private function createAlbum(Request $request, Candidature $candidat)
	{
		$filePrefix = Str::slug($candidat->getAttribute('nom') . '_' . $candidat->getAttribute('prenom'));

		// 1. Gestion des fichiers UNIQUES (avec vérification)
		$lettre = $request->hasFile('lettre_file')
			? $this->storeFile($request, 'lettre_file', static::LETTRE, $filePrefix)
			: null;

		$naissance = $request->hasFile('naissance_file')
			? $this->storeFile($request, 'naissance_file', static::NAISSANCE, $filePrefix)
			: null;

		$diplome = $request->hasFile('diplome_file')
			? $this->storeFile($request, 'diplome_file', static::DIPLOME, $filePrefix)
			: null;

		$nationalite = $request->hasFile('nationalite_file')
			? $this->storeFile($request, 'nationalite_file', static::NATIONALITE, $filePrefix)
			: null;

		$photo = $request->hasFile('photo_identite_file')
			? $this->storeFile($request, 'photo_identite_file', static::PHOTO_IDENTITE, $filePrefix)
			: null;

		$certificat_medical = $request->hasFile('certificat_medical_file')
			? $this->storeFile($request, 'certificat_medical_file', static::CERTIFICAT_MEDICAL, $filePrefix)
			: null;

		$coupon = $request->hasFile('coupon_file')
			? $this->storeFile($request, 'coupon_file', static::COUPON, $filePrefix)
			: null;

		// 2. Gestion des fichiers MULTIPLES - Bulletins
		$allBulletins = [];
		foreach (['seconde', 'premiere', 'terminale'] as $niveau) {
			$paths = $this->storeMultipleFiles(
				$request,
				"bulletins_{$niveau}",
				'bulletins',
				$niveau,
				$filePrefix
			);
			$allBulletins[$niveau] = $paths;
		}

		// 3. Gestion des fichiers MULTIPLES - Relevés BAC
		$releve_bac1_path = null;
		$releve_bac2_path = null;

		// Pour BAC1
		$bac1Paths = $this->storeMultipleFiles(
			$request,
			"releve_bac1",
			'releves',
			'bac1',
			$filePrefix
		);
		if (!empty($bac1Paths)) {
			$releve_bac1_path = $bac1Paths[0];
		}

		// Pour BAC2
		$bac2Paths = $this->storeMultipleFiles(
			$request,
			"releve_bac2",
			'releves',
			'bac2',
			$filePrefix
		);
		if (!empty($bac2Paths)) {
			$releve_bac2_path = $bac2Paths[0];
		}

		// 4. Récupérer le type de diplôme
		$type_diplome = $request->enum('type_diplome', TypeDiplomeEnum::class);

		// 5. CRÉER l'album UNE SEULE FOIS avec TOUTES les données
		$candidat->album()->create([
			'lettre' => $lettre,
			'naissance' => $naissance,
			'diplome' => $diplome,
			'nationalite' => $nationalite,
			'photo' => $photo,
			'type_diplome' => $type_diplome,
			'certificat_medical' => $certificat_medical,
			'coupon' => $coupon,
			'bulletins_lycee_paths' => !empty($allBulletins) ? json_encode($allBulletins) : null,
			'releve_bac1_path' => $releve_bac1_path,
			'releve_bac2_path' => $releve_bac2_path,
		]);
	}


	public function payementCandidaturesStore(Request $request): RedirectResponse
	{
		if (!$request->input('paye')) {
			warningMsg('La liste d\'étudiants ne peut être vide');
			return back();
		}

		CandidatureFraisPayementJob::dispatch($request->collect('paye'));

		successMsg('Liste d\'étudiants soumise avec succès');
		return to_route('admin.candidatures.participation-au-concours');
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

	public function admissionControl(Request $request): RedirectResponse
	{
		ConcoursResultJob::dispatch($request->str('admis'), $request->str('recales'));
		successMsg('Décisions appliquées avec succès. L\'envoie des messages peut prendre un peu de temps. veuillez rafraichir cette page dans quelques instants.');
		return back();
	}

	public function insertStudent(Candidature $candidature)
	{

		$fraisInscription = FraisInscription::latest()->first();
		$anneId = AnneeScolaire::where('active', true)->pluck('id')->first();

		// $group = Group::where('filiere_id', $candidature->filiere_id)->first();

		$group = FiliereGroup::where('filiere_id', $candidature->filiere_id)->first();

		if (!$candidature) {
			return;
		}


		// dump("Etudiant: " . $candidature->getAttribute('nom') . ' ' . $candidature->getAttribute('prenom'));
		if ($candidature->etudiant_id) {
			$etudiant = Etudiant::findOrFail($candidature->etudiant_id);
		} else {
			$etudiant = Etudiant::create([
				'nom' => $candidature->getAttribute('nom'),
				'nom_jeune_fille' => $candidature->getAttribute('nom_jeune_fille'),
				'prenom' => $candidature->getAttribute('prenom'),
				'genre' => $candidature->getAttribute('genre'),
				'date_naissance' => $candidature->getAttribute('date_naissance'),
				'lieu_naissance' => $candidature->getAttribute('lieu_naissance'),
				'nationalite' => $candidature->getAttribute('nationalite'),
				'tel' => $candidature->getAttribute('tel'),
				'email' => $candidature->getAttribute('email'),
				'password' => $candidature->getAttribute('password'),
				'image' => config('images.etudiants.woman'),
				'annee_admission' => $year = today()->year,
				'matricule' => Str::upper($year . '_' . fake()->unique()->randomNumber(6, true)),
			]);
		}



		$etudiant->groups()->attach(
			$group->group_id,
			[
				"annee_scolaire_id" => $anneId,
				"niveau_id" => $candidature->niveau_id
			]
		);


		Paiement::create([
			"etudiant_id" => $etudiant->id,
			"montant" => $fraisInscription->montant,
			"mode_paiement" => "caisse",
			"reference" => random_int(1000000, 99999999),
			"status" => "valide",
			"date_paiement" => now(),
			"payable_type" => FraisInscription::class,
			"payable_id" => $fraisInscription->id
		]);



		$updatedData = [
			'owner_id' => $etudiant->getAttribute('id'),
			'owner_type' => Etudiant::class,
		];

		if ($candidature->album) {
			$candidature->album->update($updatedData);
		}

		if ($candidature->responsable) {
			$candidature->responsable->update($updatedData);
		}

		if ($candidature->tuteur) {
			$candidature->tuteur->update($updatedData);
		}


		if ($candidature) {
			$candidature->update([
				'etudiant_id' => $etudiant->getAttribute('id'),
				'acceptation_date' => $now = now(),
				'end_accessibility_date' => $endAccessibilityDate = $now->addDays(3)
			]);
		}


		$etudiant->notify(new CandidatToEtudiantWelcomeNotification($etudiant->greeting()));
		// $message = $candidature->greeting();
		// $message .= '. Suite à votre admission à ' . ' ' . AppGetters::getAppName() ? AppGetters::getAppName() : "Laravel"  . ', vous avez désormais un compte étudiant. 
		// 		Ce espace candidat vous sera accessible jusqu\'au ' . $endAccessibilityDate->translatedFormat('d F Y')
		// 	. '. L\'accès à votre espace étudiant se fait avec les identifiants du présent compte candidat.';

		// $candidature->notify(new CandidatAccountLockNotification($message));
		// Msg("Operations  effectué avec succees pour:  " . $candidature->getAttribute('nom') . ' ' . $candidature->getAttribute('prenom'));

		return back()->with('success', "Etudiant inscript avec succes");
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
