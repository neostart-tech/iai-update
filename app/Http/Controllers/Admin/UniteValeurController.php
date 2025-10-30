<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitValeurRequest;
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
	public function index(): View
	{
		return view('admin.uvs.index')->with([
			'uvs' => Uv::with([
				'ue:id,code,nom,filiere_id',
				'ue.filiere:id,nom',
				'user:id,nom,prenom'
			])->get(),
		]);
	}

	public function create(): View
	{

		return view('admin.uvs.create')->with([
			'uv' => new Uv(),
			'ues' => ue::all(),
			'enseignants' => User::enseignants()->get(),
		]);
	}

	public function store(UnitValeurRequest $request): RedirectResponse
	{
		/**
		 * @var Uv $uv
		 */


		$anneeScolaireId =AnneeScolaire::where('active',true)->first(); // Ou récupère-la d'une autre manière

		$enseignantIds = $request->collect('enseignant_id')->toArray();

		 $uv = Uv::query()->create($request->except(['_token', 'ue_id', 'search_terms', 'enseignant_id',
			 'poids_devoir','poids_interrogation','poids_examen','poids_tp','poids_expose'
		 ]));
        //  $uv = Uv::query()->create($request->except(['_token', 'ue_id', 'search_terms']));


		foreach ($enseignantIds as $enseignantId) {
			UserUniteValeur::query()->create([
				'user_id' => $enseignantId,
				'unite_valeur_id' => $uv->id,
				'annee_scolaire_id' => $uv->annee_scolaire_id,
			]);
		}

		// Save optional weightings per filiere (niveau via filiere of UE)
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
					'filiere_id' => $ue->filiere_id,
				], $weights);
			}
		}

		// $uv->enseignants()->sync($request->collect('enseignant_id')->toArray());
		// $uv->enseignants()->syncWithPivotValues(
		// 	$enseignantIds,
		// 	['annee_scolaire_id' => $anneeScolaireId]
		// );

		successMsg('Unité de valeur ajoutée avec succès.');
		return to_route('admin.uvs.index');
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


public function update(UnitValeurRequest $request, Uv $uv): RedirectResponse
{
	$uv->update($request->except(['_token', 'ue_id', 'search_terms', 'enseignant_id',
		'poids_devoir','poids_interrogation','poids_examen','poids_tp','poids_expose']));


    $enseignantsSelectionnes = $request->input('enseignant_id', []);

    UserUniteValeur::where('unite_valeur_id', $uv->id)
        ->whereNotIn('user_id', $enseignantsSelectionnes)
        ->delete();

    foreach ($enseignantsSelectionnes as $enseignantId) {
        UserUniteValeur::firstorCreate(
            ['unite_valeur_id' => $uv->id, 'user_id' => $enseignantId,'annee_scolaire_id'=>$uv->annee_scolaire_id],
             
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

    return to_route('admin.uvs.index')->with(successMsg('Unité de valeur mise à jour avec succès.'));
}




	public function destroy(Request $request): RedirectResponse
	{
		$request->validate([
			"iduv" => "required"
		], [
			"iduv.required" => "L'unité de valeur est requise ou patienter jusqu'au chargement de la page"
		]);
		$uniteValeur = $request->iduv;

		$unite_valeur_note = Note::query()->where('unite_valeur_id', $uniteValeur)->get();
		$unite_valeur_emploi_du_temps = EmploiDuTemp::query()->where('uv_id', $uniteValeur)->get();

		if ($unite_valeur_note->isNotEmpty() or $unite_valeur_emploi_du_temps->isNotEmpty()) {
			return to_route('admin.uvs.index')->with(cannotDeleteItemMessage('cette unité de valeur'));
		}

		UniteValeur::query()->where('id', $uniteValeur)->first()->delete();

		return to_route('admin.uvs.index')->with(successMsg('Unité de valeur supprimée avec succès.'));
	}
}
