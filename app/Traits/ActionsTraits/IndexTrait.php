<?php

namespace App\Traits\ActionsTraits;

use App\Models\Candidature;
use App\Models\Group;
use Illuminate\View\View;
use App\Helpers\ConfigHelper as AppGetters;
use App\Http\Resources\CandidatureResource;
use App\Models\Filiere;
use App\Models\Niveau;

trait IndexTrait
{
	public function index()
	{
		$simpleCandidatures = Candidature::query()
			->where('dossier_valide', false)
			->whereNull('motif')
			->where('frais_paye', false)
			->where('participation', false)
			->where('admission', false)
			->whereDoesntHave('reorientations')
			->get();
		return CandidatureResource::collection($simpleCandidatures);
		// return view('admin.candidatures.index')->with([
		// 	'simpleCandidatures' => Candidature::query()->where('dossier_valide', false)
		// 		->whereNull('motif')
		// 		->where('frais_paye', false)
		// 		->where('participation', false)
		// 		->where('admission', false)
		// 		->whereDoesntHave('reorientations')
		// 		->get(),
		// 	'niveaux' => Niveau::all(),
		// 	'filieres' => Filiere::all(),
		// 	'metaData' => [
		// 		'title' => 'Liste des candidatures',
		// 		'breadcrumbs' => ['Administration', 'Candidatures', 'Liste'],
		// 		'page_name' => 'Liste des candidatures'
		// 	],
		// 	"viewContent" => '_simple-candidatures'
		// ]);
	}


	public function inscriptionIndexForm(): View
	{
		return view('admin.candidatures.index')->with([
			'filieres' => Filiere::all(),
			'niveaux' => Niveau::all(),
			'metaData' => [
				'title' => 'Formulaire d\'inscription',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Formulaire d\'inscription'],
				'page_name' => 'Formulaire d\'inscription'
			],
			"viewContent" => '_admin_inscription_etudiant'
		]);
	}



	public function payementCandidaturesIndex()
	{
		//  $payementCandidatures = Candidature::query()
		// ->where('dossier_valide', true)
		// ->whereNull('motif')
		// ->where('frais_paye', false)
		// ->where('participation', false)
		// ->where('admission', false)
		// ->get();
		//  return CandidatureResource::collection($payementCandidatures);

		return view('admin.candidatures.index')->with([
			'payementCandidatures' => Candidature::query()->where('dossier_valide', true)
				->whereNull('motif')
				->where('frais_paye', false)
				->where('participation', false)
				->where('admission', false)
				->get(),
			'metaData' => [
				'title' => 'Payement des frais de participation',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Payement des frais de participation'],
				'page_name' => 'Payement des frais de participation'
			],
			"viewContent" => '_payement-validation'
		]);
	}

	public function participantCandidaturesIndex()
	{

		// return CandidatureResource::collection(Candidature::query()
		// 		->where('dossier_valide', true)
		// 		->where('frais_paye', true)
		// 		->where('participation', false)
		// 		->where('admission', false)
		// 		->whereNull('motif')
		// 		->get());
		return view('admin.candidatures.index')->with([
			'participantCandidatures' => Candidature::query()
				->where('dossier_valide', true)
				->where('frais_paye', true)
				->where('participation', false)
				->where('admission', false)
				->whereNull('motif')
				->get(),
			'metaData' => [
				'title' => 'Contrôle de présence au concour',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Contrôle de présence au concour'],
				'page_name' => 'Contrôle de présence au concour'
			],
			"viewContent" => '_presence-validation'
		]);
	}

	public function admisCandidaturesIndex()
	{
		$admisCandidatures = Candidature::query()
			->where('dossier_valide', true)
			->whereNotNull('validation_date')
			->where('frais_paye', true)
			->whereNotNull('frai_paye_date')
			->where('participation', true)
			->whereNotNull('participation_date')
			->where('admission', false)
			->whereNull('admission_date')
			->whereNull('motif')
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$title = 'Admission à ' . AppGetters::getAppName();

		// return CandidatureResource::collection($admisCandidatures)
		// 	->additional([
		// 		'metaData' => [
		// 			'title' => $title,
		// 			'breadcrumbs' => ['Administration', 'Candidatures', $title],
		// 			'page_name' => $title,
		// 		],
		// 		'viewContent' => '_admission-validation',
		// 	]);


		return view('admin.candidatures.index')->with([
			'admisCandidatures' => Candidature::query()
				->where('dossier_valide', true)
				->whereNotNull('validation_date')
				->where('frais_paye', true)
				->whereNotNull('frai_paye_date')
				->where('participation', true)
				->whereNotNull('participation_date')
				->where('admission', false)
				->whereNull('admission_date')
				->whereNull('motif')
				->orderBy('nom')
				->orderBy('prenom')
				->get(),
			'metaData' => [
				'title' => $title = 'Admission à ' . AppGetters::getAppName(),
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			],
			"viewContent" => '_admission-validation'
		]);
	}


	public function InscriptionCandidaturesIndex()
	{
		$candidatures = Candidature::query()
			->where('dossier_valide', true)
			->whereNotNull('validation_date')
			->where('frais_paye', true)
			->whereNotNull('frai_paye_date')
			->where('participation', true)
			->whereNotNull('participation_date')
			->where('admission', true)
			->whereNotNull('admission_date')
			->whereNull('motif')
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$title = 'Inscription à ' . AppGetters::getAppName();

		// return CandidatureResource::collection($candidatures)
		// 	->additional([
		// 		'metaData' => [
		// 			'title' => $title,
		// 			'breadcrumbs' => ['Administration', 'Candidatures', $title],
		// 			'page_name' => $title,
		// 		],

		// 	]);


		return view('admin.candidatures.index')->with([
			'candidatures' =>  Candidature::query()
				->where('dossier_valide', true)
				->whereNotNull('validation_date')
				->where('frais_paye', true)
				->whereNotNull('frai_paye_date')
				->where('participation', true)
				->whereNotNull('participation_date')
				->where('admission', true)
				->whereNotNull('admission_date')
				->whereNull('motif')
				->where(function ($query) {
					$query->whereDoesntHave('etudiant')
						->orWhereHas('etudiant', function ($q) {
							$q->whereDoesntHave('groups');
						});
				})
				->orderBy('nom')
				->orderBy('prenom')
				->get(),
			'metaData' => [
				'title' => $title = 'Inscription à ' . AppGetters::getAppName(),
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			],
			"viewContent" => '_liste_des_amis'
		]);
	}


	public function InscriptsCandidaturesIndex()
	{
		$admisCandidatures = Candidature::query()
			->where('dossier_valide', true)
			->whereNotNull('validation_date')
			->where('frais_paye', true)
			->whereNotNull('frai_paye_date')
			->where('participation', true)
			->whereNotNull('participation_date')
			->where('admission', false)
			->whereNull('admission_date')
			->whereNull('motif')
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$title = 'Admission à ' . AppGetters::getAppName();

		// return CandidatureResource::collection($admisCandidatures)
		// 	->additional([
		// 		'metaData' => [
		// 			'title' => $title,
		// 			'breadcrumbs' => ['Administration', 'Candidatures', $title],
		// 			'page_name' => $title,
		// 		],
		// 		'viewContent' => '_admission-validation',
		// 	]);


		return view('admin.candidatures.index')->with([
			'admisCandidatures' => Candidature::query()
				->where('dossier_valide', true)
				->whereNotNull('validation_date')
				->where('frais_paye', true)
				->whereNotNull('frai_paye_date')
				->where('participation', true)
				->whereNotNull('participation_date')
				->where('admission', true)
				->whereNotNull('admission_date')
				->whereNull('motif')
				->orderBy('nom')
				->orderBy('prenom')
				->get(),
			'metaData' => [
				'title' => $title = 'Admission à ' . AppGetters::getAppName(),
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			],
			"viewContent" => '_admission-validation'
		]);
	}

	public function rectificationIndex(): View
	{
		return view('admin.candidatures.rejection-index')->with([
			'candidatures' => Candidature::query()->where('rectification_expected', true)->get(),
			'title' => 'Liste des candidatures en attente de rectification',
			'page_name' => 'Liste des candidatures en attente de rectification',
			'breadcrumbs' => [
				'Administration',
				[
					'url' => route('admin.candidatures.f'),
					'text' => 'Candidatures',
				],
				'Liste des candidatures en attente de rectification'
			]
		]);
	}

	public function rejectionIndex(): View
	{
		return view('admin.candidatures.rejection-index')->with([
			'candidatures' => Candidature::query()->where('dossier_valide', false)->whereNotNull('motif')->get()
		]);
	}

	public function chooseClassAssignmentGroupView(): View
	{
		return view('admin.candidatures.choose-class-assignment-group')->with([
			'groups' => Group::query()
				->with(['filiere:id,code'])
				->withCount('etudiants')
				->orderBy('nom')
				->get(['nom', 'filiere_id', 'slug']),
		]);
	}

	public function showGroupClassAssignmentView(Group $group): View
	{
		$candidatures = Candidature::query()
			->where('dossier_valide', true)
			->where('frais_paye', true)
			->where('participation', true)
			->where('admission', true)
			->whereNull('motif')
			->whereNull('acceptation_date')
			->whereNull('etudiant_id')
			->get();

		$candidatures = $candidatures->where('filiere_id', $group->filiere_id);

		return view('admin.candidatures.class-assignment', compact('group', 'candidatures'));
	}
}
