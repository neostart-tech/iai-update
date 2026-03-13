<?php
// app/Http/Controllers/Admin/SalleController.php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Http\Resources\SalleResource;
use App\Models\{Group, Salle, UniteValeur as UV, User};
use App\Models\EmploiDuTemp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalleController extends Controller
{
	/**
	 * Afficher la liste des salles
	 */
    // public function index()
    // {
    //     $salles = Salle::query()
    //         ->orderBy('type')
    //         ->orderBy('nom')
    //         ->get();

    //     // Compter par type
    //     $stats = [
    //         'physiques' => Salle::physiques()->count(),
    //         'virtuelles' => Salle::virtuelles()->count(),
    //         'total' => Salle::count()
    //     ];

    //     return view('admin.salles.index', compact('salles', 'stats'));
    // }

	/**
	 * API: Liste des salles en JSON
	 */
	public function index()
	{
		return SalleResource::collection(
			Salle::query()->orderBy('nom')->get()
		);
	}

	/**
	 * Afficher le formulaire de création
	 */
	public function create()
	{
		return view('admin.salles.create', [
			'plateformes' => Salle::PLATEFORMES
		]);
	}

	/**
	 * Enregistrer une nouvelle salle
	 */
	public function store(Request $request)
	{
		$nom = Str::upper($request->get('nom'));

		// Validation selon le type
		$rules = [
			'nom' => ['required', Rule::unique('salles', 'nom')],
			'type' => ['required', 'in:physique,virtuelle'],
		];

		if ($request->type === 'physique') {
			$rules['effectif'] = ['required', 'numeric', 'min:1'];
		} else {
			$rules['lien_reunion'] = ['required', 'string'];
			$rules['plateforme'] = ['nullable', 'in:' . implode(',', array_keys(Salle::PLATEFORMES))];
			$rules['instructions'] = ['nullable', 'string'];
			$rules['effectif'] = ['nullable', 'numeric', 'min:0']; // Optionnel
		}

		$request->validate($rules);

		// Préparer les données
		$data = [
			'nom' => $nom,
			'type' => $request->type,
			'effectif' => $request->effectif ?? 0,
			...injectAnneeScolaireId()
		];

		// Ajouter les champs spécifiques aux salles virtuelles
		if ($request->type === 'virtuelle') {
			$data['lien_reunion'] = $request->lien_reunion;
			$data['plateforme'] = $request->plateforme;
			$data['instructions'] = $request->instructions;
		}

		$salle = Salle::create($data);

		return new SalleResource($salle);

		// return redirect()->route('admin.salles.index')
		//     ->with('success', 'Salle créée avec succès');
	}

	/**
	 * Afficher les détails d'une salle
	 */
	public function show(Salle $salle)
	{
		// if (request()->wantsJson()) {
		// 	return new SalleResource($salle);
		// }

		return new SalleResource($salle);
	}

	/**
	 * Afficher le formulaire d'édition
	 */
	public function edit(Salle $salle)
	{
		return view('admin.salles.edit', [
			'salle' => $salle,
			'plateformes' => Salle::PLATEFORMES
		]);
	}

	/**
	 * Mettre à jour une salle
	 */
	public function update(Request $request, Salle $salle)
	{
		$nom = Str::upper($request->get('nom'));

		// Validation selon le type
		$rules = [
			'nom' => ['required', Rule::unique('salles', 'nom')->ignore($salle->id)],
			'type' => ['required', 'in:physique,virtuelle'],
		];

		if ($request->type === 'physique') {
			$rules['effectif'] = ['required', 'numeric', 'min:1'];
		} else {
			$rules['lien_reunion'] = ['required', 'string'];
			$rules['plateforme'] = ['nullable', 'in:' . implode(',', array_keys(Salle::PLATEFORMES))];
			$rules['instructions'] = ['nullable', 'string'];
			$rules['effectif'] = ['nullable', 'numeric', 'min:0'];
		}

		$request->validate($rules);

		// Préparer les données
		$data = [
			'nom' => $nom,
			'type' => $request->type,
			'effectif' => $request->effectif ?? 0,
		];

		// Gérer les champs selon le type
		if ($request->type === 'virtuelle') {
			$data['lien_reunion'] = $request->lien_reunion;
			$data['plateforme'] = $request->plateforme;
			$data['instructions'] = $request->instructions;
		} else {
			// Si on change de virtuelle à physique, effacer les champs virtuels
			$data['lien_reunion'] = null;
			$data['plateforme'] = null;
			$data['instructions'] = null;
		}

		$salle->update($data);

		return new SalleResource($salle);
	}

	/**
	 * Afficher le calendrier d'une salle
	 */
	public function displayCalendar(Salle $salle)
	{
		// return new SalleCalendarResource($salle);
		return EmploiDuTempsResource::collection($salle->emploiDuTemps);

		return view('admin.salles.calendar', compact('salle'))->with([
			'uvs' => Uv::all(),
			'types' => TypeProgrammeEnum::cases(),
			'groups' => Group::query()->with('niveau')->orderBy('nom')->get(),
			'teachers' => User::enseignants()->get(),
			'resourceUrl' => route('admin.salles.load-calendar', $salle)
		]);
	}

	/**
	 * Charger les événements du calendrier
	 */
	public function loadCalendar(Salle $salle): AnonymousResourceCollection
	{
		return EmploiDuTempsResource::collection($salle->emploiDuTemps);
	}
	/**
	 * Vérifier la disponibilité d'une salle
	 */
	public function checkAvailability(Request $request, Salle $salle)
	{
		$request->validate([
			'date' => 'required|date',
			'debut' => 'required|date_format:H:i',
			'fin' => 'required|date_format:H:i|after:debut',
			'exclude_id' => 'nullable|exists:emploi_du_temps,id'
		]);

		$debut = $request->date . ' ' . $request->debut . ':00';
		$fin = $request->date . ' ' . $request->fin . ':00';

		$query = EmploiDuTemp::where('salle_id', $salle->id)
			->where(function ($q) use ($debut, $fin) {
				$q->whereBetween('debut', [$debut, $fin])
					->orWhereBetween('fin', [$debut, $fin])
					->orWhere(function ($q) use ($debut, $fin) {
						$q->where('debut', '<=', $debut)
							->where('fin', '>=', $fin);
					});
			});

		if ($request->exclude_id) {
			$query->where('id', '!=', $request->exclude_id);
		}

		$conflit = $query->exists();

		// Pour les salles virtuelles, on peut avoir une capacité illimitée
		$capaciteOk = $salle->estVirtuelle || $salle->effectif >= ($request->effectif_requis ?? 0);

		return response()->json([
			'available' => !$conflit && $capaciteOk,
			'conflit' => $conflit,
			'capacite_ok' => $capaciteOk,
			'message' => $conflit ? 'Créneau déjà occupé' : ($capaciteOk ? 'Disponible' : 'Capacité insuffisante')
		]);
	}

	/**
	 * Supprimer une salle
	 */
	public function destroy(Salle $salle)
	{
		// Vérifier si la salle est utilisée
		$emploidutemps = EmploiDuTemp::where('salle_id', $salle->id)->exists();

		if ($emploidutemps) {
			if (request()->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => 'Impossible de supprimer cette salle car elle est utilisée dans l\'emploi du temps'
				], 404);
			}
			return back()->with('error', 'Impossible de supprimer cette salle car elle est utilisée dans l\'emploi du temps');
		}

		$salle->delete();
		return new SalleResource($salle);
	}
}
