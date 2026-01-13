<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodeRequest;
use App\Http\Resources\PeriodeResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\{Periode};

class PeriodeController extends Controller
{
	public function index()
	{

		// return PeriodeResource::collection(Periode::query()->orderByDesc('debut')->get());
		return view('admin.periodes.index')->with([
			'periodes' => Periode::query()->orderByDesc('debut')->get()
		]);
	}

	public function create(): View
	{
		return view('admin.periodes.create')->with([
			'periode' => new Periode()
		]);
	}

	public function store(PeriodeRequest $request)
	{
		$periode = Periode::create($request->all());
		// return new PeriodeResource($periode);
		return to_route('admin.periodes.index')->with(successMsg('Période ajoutée avec succès.'));
	}

	public function show(Periode $periode)
	{
		// return new PeriodeResource($periode);
		return view('admin.periodes.show', compact('periode'));
	}

	public function edit(Periode $periode): View
	{
		return view('admin.periodes.edit', compact('periode'));
	}

	public function update(PeriodeRequest $request, Periode $periode)
	{
		$periode->update($request->all());
		// return new PeriodeResource($periode);
		return to_route('admin.periodes.index')->with(successMsg('Période mise à jour avec succès.'));
	}

	public function destroy(Request $request)
	{

		$request->validate([
			"periode" => "required"
		], [
			"periode.required" => "La période est requise ou patienter jusqu'au chargement de la page"
		]);
		$periode = Periode::query()->where('id', $request->periode)->first()->delete();
		// return new PeriodeResource($periode);
		return to_route('admin.periodes.index')->with(successMsg('Période supprimée avec succès.'));
	}
}
