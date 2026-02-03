<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Http\Resources\SalleCalendarResource;
use App\Http\Resources\SalleResource;
use App\Models\{Group, Salle, UniteValeur as UV, User};
use App\Models\EmploiDuTemp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalleController extends Controller
{
	public function index()
	{
		return SalleResource::collection(Salle::query()->orderBy('nom')->get());
		// return view('admin.salles.index')->with([
		// 	'salles' => Salle::query()->orderBy('nom')->get()
		// ]);
	}

	public function store(Request $request)
	{
		$nom = Str::upper($request->get('nom'));

		$request->validate([
			'nom' => ['required', Rule::unique('salles', 'nom')->ignore($nom, 'nom')],
			'effectif' => ['required', 'numeric', 'min:1']
		]);

		$salle = Salle::query()->firstWhere('nom', $nom);

		if ($salle) {
			$salle->update([
				'nom' => $nom,
				'effectif' => $request->effectif
			]);

			return __200('Salle modifiée avec succès.');
		} else {
			$salle = Salle::query()->create([
				'nom' => $nom,
				'effectif' => $request->effectif,
				...injectAnneeScolaireId()
			]);

			return __200('Salle enregistrée avec succès.');
		}

		return new SalleResource($salle);
	}

	public function displayCalendar(Salle $salle)
	{
		// return new SalleCalendarResource($salle);

		return view('admin.salles.calendar', compact('salle'))->with([
			'uvs' => Uv::all(),
			'types' => TypeProgrammeEnum::cases(),
			'groups' => Group::query()->with('niveau')->orderBy('nom')->get(),
			'teachers' => User::enseignants()->get(),
			'resourceUrl' => route('admin.salles.load-calendar', $salle)
		]);
	}

	public function loadCalendar(Salle $salle): AnonymousResourceCollection
	{
		return EmploiDuTempsResource::collection($salle->emploiDuTemps);
	}

	public function destroy(Salle $salle)
	{
		$emploidutemps = EmploiDuTemp::query()->where('salle_id', $salle->id)->get();
		if ($emploidutemps->isNotEmpty()) {
			return __404('Impossible de supprimer cette salle');
		}
		$salle->delete();

		return new SalleResource($salle);

		// Todo Gérer la suppression des fichiers
		// return to_route('admin.salles.index')->with(successMsg('Salle supprimée avec succès.'));
	}
}
