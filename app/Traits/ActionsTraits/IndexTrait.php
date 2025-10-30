<?php

namespace App\Traits\ActionsTraits;

use App\Models\Candidature;
use App\Models\Group;
use Illuminate\View\View;
use App\Helpers\ConfigHelper as AppGetters;
use App\Models\Filiere;
use App\Models\Niveau;

trait IndexTrait
{
	public function index(): View
	{
		return view('admin.candidatures.index')->with([
			'simpleCandidatures' => Candidature::query()->where('dossier_valide', false)
				->whereNull('motif')
				->where('frais_paye', false)
				->where('participation', false)
				->where('admission', false)
				->get(),
				'niveaux'=>Niveau::all(),
				'filieres'=>Filiere::all(),
			'metaData' => [
				'title' => 'Liste des candidatures',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Liste'],
				'page_name' => 'Liste des candidatures'
			],
			"viewContent" => '_simple-candidatures'
		]);
	}


	public function payementCandidaturesIndex(): View
	{
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

	public function participantCandidaturesIndex(): View
	{
		return view('admin.candidatures.index')->with([
			'participantCandidatures' => Candidature::query()
				->where('dossier_valide', true)
				->where('frais_paye', true)
				->where('participation', false)
				->whereNull('participation_date')
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

	public function admisCandidaturesIndex(): View
	{
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

	public function rectificationIndex(): View
	{
		return view('admin.candidatures.rejection-index')->with([
			'candidatures' => Candidature::query()->where('rectification_expected', true)->get(),
			'title' => 'Liste des candidatures en attente de rectification',
			'page_name' => 'Liste des candidatures en attente de rectification',
			'breadcrumbs' => [
				'Administration',
				[
					'url' => route('admin.candidatures.index'),
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
		$candidatures=Candidature::query()
			->where('dossier_valide', true)
			->where('frais_paye', true)
			->where('participation', true)
			->where('admission', true)
			->whereNull('motif')
			->whereNull('acceptation_date')
			->whereNull('etudiant_id')
			->get();

			$candidatures = $candidatures->where('filiere_id', $group->filiere_id);

		return view('admin.candidatures.class-assignment', compact('group','candidatures'));
	}
}
