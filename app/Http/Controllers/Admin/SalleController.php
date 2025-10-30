<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\EmploiDuTempsResource;
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
	public function index(): View
	{
		return view('admin.salles.index')->with([
			'salles' => Salle::query()->orderBy('nom')->get()
		]);
	}

	public function store(Request $request): RedirectResponse
	{
		$nom = Str::upper($request->get('nom'));

		$request->validate([
			'nom' => ['required', Rule::unique('salles')->ignore($nom, 'nom')],
			'effectif' => ['required', 'numeric', 'min:1']
		], [
			'nom.required' => 'Le nom de la salle est obligatoire',
			'nom.unique' => 'Une salle portant le même nom existe déjà',
			'effectif.required' => 'L\'effectif de la salle est obligatoire',
			'effectif.numeric' => 'L\'effectif de la salle doit être un nombre entier',
			'effectif.min' => 'L\'effectif de la salle doit être supérieur ou égal à 1',
		]);

		$salle = Salle::query()->firstWhere('nom', $nom);

		if ($salle) {
			$salle->update($request->only(['nom', 'effectif']));
			successMsg('Salle modifiée avec succès.');
		} else {
			Salle::query()->create([
				'nom' => $nom,
				'effectif' => $request->get('effectif'),
				...injectAnneeScolaireId()
			]);
			successMsg('Salle enrégistrée avec succès.');
		}

		return to_route('admin.salles.index');
	}

	public function displayCalendar(Salle $salle): View
	{
		return view('admin.salles.calendar', compact('salle'))->with([
			'uvs' => Uv::all(),
			'types' => TypeProgrammeEnum::cases(),
			'groups' => Group::query()->with('filiere:id,code,slug')->orderBy('nom')->get(),
			'teachers' => User::enseignants()->get(),
			'resourceUrl' => route('admin.salles.load-calendar', $salle)
		]);
	}

	public function loadCalendar(Salle $salle): AnonymousResourceCollection
	{
		return EmploiDuTempsResource::collection($salle->emploiDuTemps);
	}

	public function destroy(Request $request): RedirectResponse
	{
		$salle = $request->deleteSalleForm;
		$emploidutemps = EmploiDuTemp::query()->where('salle_id', $salle)->get();
		if ($emploidutemps->isNotEmpty()) {
			return back()->with(cannotDeleteItemMessage('cette salle'));
		}
		Salle::query()->where('id', $salle)->first()->delete();


		// Todo Gérer la suppression des fichiers
		return to_route('admin.salles.index')->with(successMsg('Salle supprimée avec succès.'));
	}
}
