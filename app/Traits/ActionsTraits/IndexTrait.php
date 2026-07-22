<?php

namespace App\Traits\ActionsTraits;

use App\Models\Candidature;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Helpers\ConfigHelper as AppGetters;
use App\Http\Resources\CandidatureResource;
use App\Models\Filiere;
use App\Models\Niveau;

trait IndexTrait
{
	public function index()
	{
		$candidatures = Candidature::query()
			->with(['niveau', 'filiere', 'album'])
			->orderBy('nom')
			->orderBy('prenom')
			->get();
		return CandidatureResource::collection($candidatures);
	}

	/**
	 * Nombre de candidatures en attente d'action pour l'utilisateur connecté :
	 * - Chargé de la clientèle : dossiers pas encore transmis à l'académie.
	 * - Directeur académique / Logisticien académique : dossiers transmis,
	 *   en attente de la décision finale.
	 */
	public function countCandidaturesATraiter(Request $request)
	{
		$roleSlugs = $request->user()->roles->pluck('slug');

		$query = Candidature::query()
			->whereNull('motif')
			->where('rectification_expected', false)
			->where('dossier_valide', false);

		if ($roleSlugs->contains('directeur-academique') || $roleSlugs->contains('logiticien-academique')) {
			$query->where('transmis_academie', true);
		} elseif ($roleSlugs->contains('charge-de-la-clientele')) {
			$query->where('transmis_academie', false);
		} else {
			return response()->json(['count' => 0]);
		}

		return response()->json(['count' => $query->count()]);
	}

	public function exportEtudeDossierExcel(Request $request)
	{
		$query = Candidature::query()
			->with(['niveau', 'filiere', 'album', 'tuteurs', 'submittedDocuments']);

		if ($request->filled('ids')) {
			$query->whereIn('id', (array) $request->input('ids'));
		}

		$candidatures = $query
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$fileName = 'candidatures_etude_dossier_' . now()->format('Y-m-d') . '.xlsx';

		return \Maatwebsite\Excel\Facades\Excel::download(
			new \App\Exports\CandidaturesEtudeDossierExport($candidatures),
			$fileName
		);
	}


	public function inscriptionIndexForm()
	{
		return response()->json([
			'filieres' => Filiere::all(),
			'niveaux' => Niveau::all(),
			'metaData' => [
				'title' => 'Formulaire d\'inscription',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Formulaire d\'inscription'],
				'page_name' => 'Formulaire d\'inscription'
			]
		]);
	}



	public function payementCandidaturesIndex()
	{
		$payementCandidatures = Candidature::query()->with(['niveau', 'filiere', 'album'])
			->where('dossier_valide', true)
			->whereNull('motif')
			->where('frais_paye', false)
			->where('participation', false)
			->where('admission', false)
			->get();

		return response()->json([
			'data' => CandidatureResource::collection($payementCandidatures),
			'metaData' => [
				'title' => 'Payement des frais de participation',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Payement des frais de participation'],
				'page_name' => 'Payement des frais de participation'
			]
		]);
	}

	public function participantCandidaturesIndex()
	{
		$participantCandidatures = Candidature::query()
			->where('dossier_valide', true)
			->where('frais_paye', true)
			->where('participation', false)
			->where('admission', false)
			->whereNull('motif')
			->get();

		return response()->json([
			'data' => CandidatureResource::collection($participantCandidatures),
			'metaData' => [
				'title' => 'Contrôle de présence au concour',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Contrôle de présence au concour'],
				'page_name' => 'Contrôle de présence au concour'
			]
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

		return response()->json([
			'data' => CandidatureResource::collection($admisCandidatures),
			'metaData' => [
				'title' => $title,
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			]
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
			->where(function ($query) {
				$query->whereDoesntHave('etudiant')
					->orWhereHas('etudiant', function ($q) {
						$q->whereDoesntHave('groups');
					});
			})
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$title = 'Inscription à ' . AppGetters::getAppName();

		return response()->json([
			'data' => CandidatureResource::collection($candidatures),
			'metaData' => [
				'title' => $title,
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			]
		]);
	}


	public function exportCandidatsAdmisExcel()
	{
		$candidatures = Candidature::query()
			->with(['niveau', 'filiere'])
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
			->get();

		$fileName = 'candidats_admis_' . now()->format('Y-m-d') . '.xlsx';

		return \Maatwebsite\Excel\Facades\Excel::download(
			new \App\Exports\CandidatsAdmisExport($candidatures),
			$fileName
		);
	}

	public function liste_des_admis()
	{
		$admisCandidatures = Candidature::query()
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

		$title = 'Admis à ' . AppGetters::getAppName();

		return response()->json([
			'data' => CandidatureResource::collection($admisCandidatures),
			'metaData' => [
				'title' => $title,
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			]
		]);
	}

	public function liste_des_rectifications()
	{
		return response()->json([
			'data' => CandidatureResource::collection(Candidature::query()->where('rectification_expected', true)->get()),
			'metaData' => [
				'title' => 'Liste des candidatures en attente de rectification',
				'page_name' => 'Liste des candidatures en attente de rectification'
			]
		]);
	}

	public function liste_des_rejets()
	{
		return response()->json([
			'data' => CandidatureResource::collection(Candidature::query()->where('dossier_valide', false)->whereNotNull('motif')->get())
		]);
	}

	public function chooseClassAssignmentGroupView()
	{
		return response()->json([
			'groups' => Group::query()
				->with(['filieres:id,code,nom', 'niveau:id,libelle'])
				->withCount('etudiants')
				->orderBy('nom')
				->get(['id', 'nom', 'slug', 'niveau_id']),
		]);
	}

	public function showGroupClassAssignmentView(Group $group)
	{
		$candidatures = Candidature::query()
			->where('dossier_valide', true)
			->where('frais_paye', true)
			->where('participation', true)
			->where('admission', true)
			->whereNull('motif')
			->whereNull('acceptation_date')
			->whereNull('etudiant_id')
			->whereIn('filiere_id', $group->filieres()->pluck('filieres.id'))
			->get();

		return response()->json([
			'group' => $group->load('filieres:id,code,nom'),
			'candidatures' => CandidatureResource::collection($candidatures)
		]);
	}
}
