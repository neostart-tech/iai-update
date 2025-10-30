<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitEnseignementRequest;
use App\Models\UniteEnseignement;
use App\Models\UniteValeur;
use Illuminate\Http\RedirectResponse;
use App\Models\{Filiere, Periode, UniteEnseignement as Ue};
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniteEnseignementController extends Controller
{
	public function index(): View
	{
		return view('admin.ues.index')->with([
			'ues' => Ue::with(['filiere:id,code,nom','periode:id,nom'])->orderBy('nom')->get()
		]);
	}

	public function create(): View
	{
		return view('admin.ues.create')->with([
			'ue' => new Ue(),
			'periodes' => Periode::query()->orderByDesc('debut')->get(),
			'filieres' => Filiere::query()->orderBy('nom')->get()
		]);
	}

	public function store(UnitEnseignementRequest $request): RedirectResponse
	{
		Ue::create([
			...$request->only([
				'nom',
				'code',
				'credit',
				'periode_id',
				'filiere_id',
			]),
			...injectAnneeScolaireId()
		]);

		return to_route('admin.ues.index')->with(successMsg('Unité d\'enseignement ajoutée avec succès.'));
	}

	public function show(Ue $uniteEnseignement): View
	{
		return view('admin.ues.show', compact('uniteEnseignement'));
	}

	public function edit(Ue $ue): View
	{
		return view('admin.ues.edit', compact('ue'))->with([
			'periodes' => Periode::query()->orderByDesc('debut')->get(),
			'filieres' => Filiere::query()->orderBy('nom')->get()
		]);
	}

	public function update(UnitEnseignementRequest $request, Ue $ue): RedirectResponse
	{
		$ue->update([
			...$request->only([
				'nom',
				'code',
				'credit',
				'periode_id',
				'filiere_id',
			]),
			...injectAnneeScolaireId()
		]);
		return to_route('admin.ues.index')->with(successMsg('Unité d\'enseignement mise à jour avec succès.'));
	}

	public function destroy(Request $request): RedirectResponse
	{
		$ue= intval($request->idue);
		$uniteDeValeurs=UniteValeur::query()->where('unite_enseignement_id',$ue)->get();
		if ($uniteDeValeurs->isNotEmpty()) {
			return to_route('admin.ues.index')->with(cannotDeleteItemMessage('cette unité d\'enseignement'));
		}
		UniteEnseignement::query()->where('id',$ue)->first()->delete();
		return to_route('admin.ues.index')->with(successMsg('Unité d\'enseignement supprimée avec succès.'));
	}
}
