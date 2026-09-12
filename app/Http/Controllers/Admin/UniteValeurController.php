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
use Illuminate\Http\JsonResponse;


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

	public function create(Request $request)
	{
		$anneeScolaireId = AnneeScolaire::where('active', true)->value('id');
		$data = [
			'ues' => Ue::all(),
			'periodes' => Periode::where("annee_scolaire_id", $anneeScolaireId)->get(),
			'enseignants' => User::enseignants()->get(),
		];
		
		if ($request->wantsJson() || $request->is('api/*')) {
			return response()->json($data);
		}

		return view('admin.uvs.create')->with(array_merge(['uv' => new Uv()], $data));
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
		
		// Fallback to UE relations if not provided
		$ue = Ue::with('filiere')->find($request->integer('ue_id'));
		if ($ue) {
			if (empty($filiereIds) && $ue->filiere_id) {
				$filiereIds = [$ue->filiere_id];
			}
			if (empty($periodeIds) && $ue->periode_id) {
				$periodeIds = [$ue->periode_id];
			}
			if (empty($niveauIds) && $ue->filiere && $ue->filiere->niveau_id) {
				$niveauIds = [$ue->filiere->niveau_id];
			} elseif(empty($niveauIds)) {
				// Default to 1 to prevent empty loop if no niveau is found
				$niveauIds = [1];
			}
		}

		if (empty($filiereIds)) $filiereIds = [null];
		if (empty($niveauIds)) $niveauIds = [null];
		if (empty($periodeIds)) $periodeIds = [null];

		$createdUvs = [];

		foreach ($filiereIds as $filiereId) {
			foreach ($niveauIds as $niveauId) {
				foreach ($periodeIds as $periodeId) {
					$data = $request->except([
						'id',
						'slug',
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
					// $data no longer has 'nom' and 'code' from request since it's now in matieres
					// But we must make sure 'matiere_id' is passed.
					if ($request->has('matiere_id')) {
						$data['matiere_id'] = $request->input('matiere_id');
					}
					$data['filiere_id'] = $filiereId;
					$data['niveau_id'] = $niveauId;
					$data['periode_id'] = $periodeId;
					
					// Force the trait to generate a new slug for each instance (though removed from table, let's keep it if trait tries to use it or just let trait fail, wait trait requires a field? We removed slug from unite_valeurs)
					// $data['slug'] = null; // Removed from DB

					// Prevent duplicates
					$existingUv = Uv::query()->where([
						'matiere_id' => $data['matiere_id'] ?? null,
						'filiere_id' => $filiereId,
						'niveau_id' => $niveauId,
						'periode_id' => $periodeId,
					])->first();

					if ($existingUv) {
						$existingUv->update($data);
						$uv = $existingUv;
					} else {
						$uv = Uv::query()->create($data);
					}

					foreach ($enseignantIds as $enseignantId) {
						UserUniteValeur::query()->firstOrCreate([
							'user_id' => $enseignantId,
							'unite_valeur_id' => $uv->id,
							'annee_scolaire_id' => $uv->annee_scolaire_id,
						]);
					}

					// Save optional weightings per filiere
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

	public function edit(Request $request, Uv $uv)
	{
		$enseignants = UserUniteValeur::query()
			->with(['user'])
			->where('unite_valeur_id', $uv->id)
			->get();

		$enseignantsSelected = $enseignants->pluck('user_id')->toArray();

		$data = [
			'uv' => $uv,
			'ues' => Ue::all(),
			'enseignants' => User::enseignants()->get(),
			'enseignantsSelected' => $enseignantsSelected,
		];

		if ($request->wantsJson() || $request->is('api/*')) {
			return response()->json($data);
		}

		return view('admin.uvs.edit', $data);
	}


	public function update(UnitValeurRequest $request, Uv $uv)
	{
		$data = $request->except([
			'id',
			'slug',
			'_token',
			'ue_id',
			'search_terms',
			'enseignant_id',
			'filiere_ids',
			'niveau_ids',
			'periode_ids',
			'poids_devoir',
			'poids_interrogation',
			'poids_examen',
			'poids_tp',
			'poids_expose'
		]);

		if ($request->filled('filiere_id')) {
			$data['filiere_id'] = $request->input('filiere_id');
		} elseif ($request->filled('filiere_ids') && is_array($request->input('filiere_ids')) && count($request->input('filiere_ids')) > 0) {
			$data['filiere_id'] = $request->input('filiere_ids')[0];
		}

		if ($request->filled('niveau_id')) {
			$data['niveau_id'] = $request->input('niveau_id');
		} elseif ($request->filled('niveau_ids') && is_array($request->input('niveau_ids')) && count($request->input('niveau_ids')) > 0) {
			$data['niveau_id'] = $request->input('niveau_ids')[0];
		}

		if ($request->filled('periode_id')) {
			$data['periode_id'] = $request->input('periode_id');
		} elseif ($request->filled('periode_ids') && is_array($request->input('periode_ids')) && count($request->input('periode_ids')) > 0) {
			$data['periode_id'] = $request->input('periode_ids')[0];
		}

		$uv->update($data);


		$enseignantsSelectionnes = $request->input('enseignant_id', []);

		UserUniteValeur::where('unite_valeur_id', $uv->id)
			->whereNotIn('user_id', $enseignantsSelectionnes)
			->delete();

		foreach ($enseignantsSelectionnes as $enseignantId) {
			UserUniteValeur::firstorCreate(
				['unite_valeur_id' => $uv->id, 'user_id' => $enseignantId, 'annee_scolaire_id' => $uv->annee_scolaire_id],

			);
		}

		// Save optional weightings per filiere
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
				'filiere_id' => $uv->filiere_id,
			], $weights);
		}

		if ($request->wantsJson() || $request->is('api/*')) {
			return response()->json(['success' => true, 'uv' => $uv]);
		}

		return redirect()->route('admin.uvs.index')
			->with('success', 'Unité de valeur mise à jour avec succès.');
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
