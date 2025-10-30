<?php

namespace App\Http\Controllers;

use App\Enums\TypeDiplomeEnum;
use App\Http\Requests\Candidature\StoreRequest;
use App\Notifications\Candidatures\CandidatAbsentNotification;
use App\Notifications\Candidatures\CandidatPresentNotification;
use App\Jobs\{CandidatureFraisPayementJob, ConcoursResultJob, SmsSendingProcess};
use App\Models\{Candidature, CandidatureDocument, Inscription};
use App\Notifications\Candidatures\CandidatWelcomeNotification;
use App\Traits\ActionsTraits\{IndexTrait,CandidatureFirstValidationTrait};
use App\Traits\FileManagementTrait;
use App\Models\Niveau;
use App\Models\Filiere;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Support\Str;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade;
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

	public function show(Candidature $candidature): View
	{
		$candidature->load(['documents', 'album']);

		return view('admin.candidatures.show', compact('candidature'))->with([
			'album' => $candidature->album,
			'niveau' => $candidature->niveau,
			'filiere' => $candidature->filiere,
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

	public function store(StoreRequest $request): RedirectResponse
	{
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

		self::createAlbum($request, $candidat);

		Auth::guard('web_candidatures')->login($candidat);

		$message = $candidat->greeting(true);
		$message .= ", votre dossier de candidature a été déposé avec succès.";

		// SmsSendingProcess::dispatch(
		// 	$candidat->getAttribute('tel'),
		// 	'Votre dossier de candidature a été déposé avec succès. Connectez-vous régulièrement en vous rendant sur ' . route('officiel.login') . '	à votre compte pour suivre le niveau d\'avancement de votre candidature'
		// );

		$candidat->notify(new CandidatWelcomeNotification($candidat->greeting(true), $message));

		return to_route('officiel.my-space.show')->with(successMsg('Votre dossier a été déposé avec succès.'));
	}

	private function createAlbum(StoreRequest $request, Candidature $candidat)
	{
		$filePrefix = Str::slug($candidat->getAttribute('nom') . '_' . $candidat->getAttribute('prenom'));

		$coupon = null;
		// Lettre manuscrite
		$lettre = $this->storeFile(
			$request,
			'lettre_file',
			static::LETTRE,
			$filePrefix
		);

		// Naissance
		$naissance = $this->storeFile(
			$request,
			'naissance_file',
			static::NAISSANCE,
			$filePrefix
		);

		// Diplôme
		$diplome = $this->storeFile(
			$request,
			'diplome_file',
			static::DIPLOME,
			$filePrefix
		);

		// Nationalité
		$nationalite = $this->storeFile(
			$request,
			'nationalite_file',
			static::NATIONALITE,
			$filePrefix
		);

		// Photo d'identité
		$photo = $this->storeFile(
			$request,
			'photo_identite_file',
			static::PHOTO_IDENTITE,
			$filePrefix
		);

		// Certificat médical
		$certificat_medical = $this->storeFile(
			$request,
			'certificat_medical_file',
			static::CERTIFICAT_MEDICAL,
			$filePrefix
		);

		// Certificat médical
		if ($request->hasFile('coupon_file')) {
			$coupon = $this->storeFile(
				$request,
				'coupon_file',
				static::COUPON,
				$filePrefix
			);
		}

		// Bulletins multi-fichiers (seconde, premiere, terminale)
		foreach (['seconde', 'premiere', 'terminale'] as $niveau) {
			$key = "bulletins_{$niveau}";
			if ($request->hasFile($key)) {
				foreach ($request->file($key) as $file) {
					if (!$file) continue;
					$name = uniqid($filePrefix . '_') . '.' . $file->getClientOriginalExtension();
					$path = $file->storeAs("bulletins/{$niveau}", $name, 'public');
					$candidat->documents()->create([
						'type' => 'bulletin',
						'niveau' => $niveau,
						'path' => $path,
					]);
				}
			}
		}

		// Relevés BAC (bac1, bac2)
		foreach ([1, 2] as $i) {
			$key = "releve_bac{$i}";
			$niveau = "bac{$i}";
			if ($request->hasFile($key)) {
				foreach ($request->file($key) as $file) {
					if (!$file) continue;
					$name = uniqid($filePrefix . '_') . '.' . $file->getClientOriginalExtension();
					$path = $file->storeAs("releves/{$niveau}", $name, 'public');
					$candidat->documents()->create([
						'type' => 'releve',
						'niveau' => $niveau,
						'path' => $path,
					]);
				}
			}
		}

		$type_diplome = $request->enum('type_diplome', TypeDiplomeEnum::class);

		$candidat->album()->create(compact(
			'lettre',
			'naissance',
			'diplome',
			'nationalite',
			'photo',
			'type_diplome',
			'certificat_medical',
			'coupon',
		));

		// Alimente le dashboard des inscriptions
		$docs = $candidat->documents()->get();
		$bulletins = $docs->where('type', 'bulletin')->pluck('path')->values()->all();
		$bac1 = $docs->firstWhere('type', 'releve')?->path;
		$bac1 = $docs->firstWhere(fn($d) => $d->type === 'releve' && $d->niveau === 'bac1')?->path ?? $bac1;
		$bac2 = $docs->firstWhere(fn($d) => $d->type === 'releve' && $d->niveau === 'bac2')?->path;

		Inscription::create([
			'numero_table' => $request->string('numero_table'),
			'annee_bac' => (int) $request->input('annee_bac'),
			'lettre_motivation' => $request->string('lettre_motivation'),
			'serie' => $request->string('serie'),
			'email' => $request->filled('email') ? $request->string('email') : null,
			'phone1' => $request->string('tel'),
			'phone2' => $request->string('tel2'),
			'phone3' => $request->string('tel3'),
			'tuteur_lieu' => $request->string('adresse_tuteur'),
			'accepte' => $request->boolean('accept_cgu') ? 'accepte' : 'refuse',
			'certificat_medical_path' => $certificat_medical ?: null,
			'bulletins_lycee_paths' => $bulletins,
			'releve_bac1_path' => $bac1 ?? null,
			'releve_bac2_path' => $bac2 ?? null,
			'status' => 'pending',
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


	public function getNiveaux()
	{
		return Niveau::query()->orderBy('libelle')->get();
	}

	public function getFilieres()
	{
		return Filiere::query()->orderBy('nom')->get();
	}


}
