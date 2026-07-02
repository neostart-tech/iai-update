<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitValeurRequest;
use App\Http\Resources\UvResource;
use App\Models\{AnneeScolaire, Periode, UniteEnseignement as Ue, UniteValeur as Uv, User};
use App\Models\UVWeighting;
use App\Models\EmploiDuTemp;
use App\Models\Note;
use App\Models\UniteValeur;
use App\Models\UserUniteValeur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;


class UniteValeurController extends Controller
{
	public function index()
	{

		return UvResource::collection(Uv::with([
			'ue',
			'user',
			'filiere',
			'periode',
		])->get());

		// return view('admin.uvs.index')->with([
		// 	'uvs' => Uv::with([
		// 		'ue:id,code,nom,filiere_id',
		// 		'ue.filiere:id,nom',
		// 		'user:id,nom,prenom'
		// 	])->get(),
		// ]);
	}

	public function create(): View
	{
		$anneeScolaireId = AnneeScolaire::where('active', true)->value('id');
		return view('admin.uvs.create')->with([
			'uv' => new Uv(),
			'ues' => Ue::all(),
			"periodes" => Periode::where("annee_scolaire_id", $anneeScolaireId)->get(),
			'enseignants' => User::enseignants()->get(),
		]);
	}

	public function store(UnitValeurRequest $request)
	{
		$anneeScolaireId = AnneeScolaire::where('active', true)->first();

		$enseignantIds = $request->collect('enseignant_id')->toArray();
		
		$filiereIds = $request->input('filiere_ids', []);
		$niveauIds = $request->input('niveau_ids', []);
		$periodeIds = $request->input('periode_ids', []);

		if (empty($filiereIds) && $request->filled('filiere_id')) {
			$filiereIds = [$request->input('filiere_id')];
		}
		if (empty($niveauIds) && $request->filled('niveau_id')) {
			$niveauIds = [$request->input('niveau_id')];
		}
		if (empty($periodeIds) && $request->filled('periode_id')) {
			$periodeIds = [$request->input('periode_id')];
		}
		
		$createdUvs = [];

		foreach ($filiereIds as $filiereId) {
			foreach ($niveauIds as $niveauId) {
				foreach ($periodeIds as $periodeId) {
					$data = $request->except([
						'_token',
						'ue_id',
						'enseignant_id',
						'filiere_ids',
						'niveau_ids',
						'periode_ids',
						'filiere_id',
						'niveau_id',
						'periode_id',
						'search_terms',
						'poids_devoir',
						'poids_interrogation',
						'poids_examen',
						'poids_tp',
						'poids_expose'
					]);
					
					$data['filiere_id'] = $filiereId;
					$data['niveau_id'] = $niveauId;
					$data['periode_id'] = $periodeId;
					
					// Force the trait to generate a new slug for each instance
					$data['slug'] = null; 

					$uv = Uv::query()->create($data);

					foreach ($enseignantIds as $enseignantId) {
						UserUniteValeur::query()->create([
							'user_id' => $enseignantId,
							'unite_valeur_id' => $uv->id,
							'annee_scolaire_id' => $uv->annee_scolaire_id,
						]);
					}

					// Save optional weightings per filiere
					$ue = Ue::find($request->integer('ue_id'));
					if ($ue) {
						$weights = [
							'devoir' => (int) $request->input('poids_devoir', 0),
							'interrogation' => (int) $request->input('poids_interrogation', 0),
							'examen' => (int) $request->input('poids_examen', 0),
							'tp' => (int) $request->input('poids_tp', 0),
							'expose' => (int) $request->input('poids_expose', 0),
						];
						$sum = array_sum($weights);
						if ($sum === 0 || $sum === 100) {
							UVWeighting::updateOrCreate([
								'unite_valeur_id' => $uv->id,
								'filiere_id' => $filiereId,
							], $weights);
						}
					}

					$createdUvs[] = $uv;
				}
			}
		}

		// Retourne la première UV créée pour que le front ne plante pas (bien qu'on recharge la liste ensuite)
		return new UvResource($createdUvs[0] ?? new Uv());
	}

	public function show(Uv $uniteValeur): View
	{
		return view('admin.uvs._show-modal', compact('uniteValeur'));
	}

	public function edit(Uv $uv): View
	{
		$enseignants = UserUniteValeur::query()
			->with(['user'])
			->where('unite_valeur_id', $uv->id)
			->get();

		$enseignantsSelected = $enseignants->pluck('user_id')->toArray();

		return view('admin.uvs.edit', [
			'uv' => $uv,
			'ues' => Ue::all(),
			'enseignants' => User::enseignants()->get(),
			'enseignantsSelected' => $enseignantsSelected,
		]);
	}


	public function update(UnitValeurRequest $request, Uv $uv)
	{
		$uv->update($request->except([
			'_token',
			'ue_id',
			'search_terms',
			'enseignant_id',
			'poids_devoir',
			'poids_interrogation',
			'poids_examen',
			'poids_tp',
			'poids_expose'
		]));


		$enseignantsSelectionnes = $request->input('enseignant_id', []);

		UserUniteValeur::where('unite_valeur_id', $uv->id)
			->whereNotIn('user_id', $enseignantsSelectionnes)
			->delete();

		foreach ($enseignantsSelectionnes as $enseignantId) {
			UserUniteValeur::firstorCreate(
				['unite_valeur_id' => $uv->id, 'user_id' => $enseignantId, 'annee_scolaire_id' => $uv->annee_scolaire_id],

			);
		}

		// Update weighting for this UV/filiere
		$ue = Ue::find($request->integer('ue_id')) ?? $uv->ue;
		if ($ue) {
			$weights = [
				'devoir' => (int) $request->input('poids_devoir', 0),
				'interrogation' => (int) $request->input('poids_interrogation', 0),
				'examen' => (int) $request->input('poids_examen', 0),
				'tp' => (int) $request->input('poids_tp', 0),
				'expose' => (int) $request->input('poids_expose', 0),
			];
			$sum = array_sum($weights);
			if ($sum === 0 || $sum === 100) {
				UVWeighting::updateOrCreate([
					'unite_valeur_id' => $uv->id,
					'filiere_id' => $ue->filiere_id,
				], $weights);
			}
		}
		return new UvResource($uv);

		// return to_route('admin.uvs.index')->with(successMsg('Unité de valeur mise à jour avec succès.'));
	}




	// public function destroy(Request $request)
	// {
	// 	$request->validate([
	// 		"iduv" => "required"
	// 	], [
	// 		"iduv.required" => "L'unité de valeur est requise ou patienter jusqu'au chargement de la page"
	// 	]);
	// 	$uniteValeur = $request->iduv;

	// 	$unite_valeur_note = Note::query()->where('unite_valeur_id', $uniteValeur)->get();
	// 	$unite_valeur_emploi_du_temps = EmploiDuTemp::query()->where('uv_id', $uniteValeur)->get();

	// 	if ($unite_valeur_note->isNotEmpty() or $unite_valeur_emploi_du_temps->isNotEmpty()) {
	// 		return to_route('admin.uvs.index')->with(cannotDeleteItemMessage('cette unité de valeur'));
	// 	}

	// 	$uv = UniteValeur::query()->where('id', $uniteValeur)->first()->delete();
	// 	// return new UvResource($uv);

	// 	return to_route('admin.uvs.index')->with(successMsg('Unité de valeur supprimée avec succès.'));
	// }
	public function destroy(UniteValeur $uv)
	{



		$unite_valeur_note = Note::query()->where('unite_valeur_id', $uv->id)->get();
		$unite_valeur_emploi_du_temps = EmploiDuTemp::query()->where('uv_id', $uv->id)->get();

		if ($unite_valeur_note->isNotEmpty() or $unite_valeur_emploi_du_temps->isNotEmpty()) {
			return __422('Impossible de supprimer');
			// return to_route('admin.uvs.index')->with(cannotDeleteItemMessage('cette unité de valeur'));
		}

		$uv->delete();
		return new UvResource($uv);

		// return to_route('admin.uvs.index')->with(successMsg('Unité de valeur supprimée avec succès.'));
	}
}
