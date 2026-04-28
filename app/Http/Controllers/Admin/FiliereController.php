<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiliereRequest;
use App\Http\Resources\FiliereResource;
use App\Models\{AnneeScolaire, Filiere};
use App\Models\Grade;
use App\Traits\FileManagementTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
	use FileManagementTrait;

	//  public function __construct()
	//  {
	//  	$this->authorizeResource(Filiere::class, 'filiere');
	//  }

	private const FOLDER = 'filieres';

	public function index(Request $request)
	{
		$anneeActiveId = AnneeScolaire::where('active', true)->value('id');
		$niveauId = $request->query('niveau_id');

		$query = Filiere::query();

		if ($niveauId) {
			$query->whereHas('groups', function ($q) use ($niveauId, $anneeActiveId) {
				$q->where('niveau_id', $niveauId);
				if ($anneeActiveId) {
					$q->where('annee_scolaire_id', $anneeActiveId);
				}
			});
		}

		return FiliereResource::collection($query->with(['anneesScolaires' => function ($q) use ($anneeActiveId) {
			$q->where('annee_filiere.annee_scolaire_id', $anneeActiveId)->get();
		}])->withCount([
			'etudiants as etudiants_count' => function ($query) use ($anneeActiveId) {
				$query->where('etudiant_group.annee_scolaire_id', $anneeActiveId)
					->distinct('etudiants.id');
			}
		])->get()->reverse());
	}

	public function create(): View
	{
		return view('admin.filieres.create')->with([
			'filiere' => new Filiere(),
			'annee' => null,
			'annee_scolaires' => AnneeScolaire::all()
		]);
	}

	public function store(FiliereRequest $request)
	{

		$anneeActiveId = AnneeScolaire::where('active', true)->first()->getAttribute('id');

		$filePath = $request->hasFile('image') ? $this->storeFile($request, 'image', static::FOLDER) : config('images.filieres.default');

		$request->merge(['image' => $filePath]);
		$filiere = Filiere::create($request->except([
			'date_debut',
			'date_fin',
			'inscrits'

		]));

		if (\AppGetters::getAfficherChoixDate()) {
			$filiere->anneesScolaires()->attach(
				$anneeActiveId,
				[
					'date_debut' => $request->date_debut,
					'date_fin'   => $request->date_fin,
				]
			);
		}



		return new FiliereResource($filiere);


		// return to_route('admin.filieres.index')->with(successMsg('Filière ajoutée avec succès.'));
	}

	public function show(Filiere $filiere)
	{
		// return new FiliereResource($filiere);

		return view('admin.filieres.show', compact('filiere'));
	}

	public function edit(Filiere $filiere): View
	{
		$anneeScolaireId = injectAnneeScolaireId();
		$annee = $filiere->anneesScolaires()
			->where('annee_scolaire_id', $anneeScolaireId)
			->first();
		return view('admin.filieres.edit', compact('filiere', 'annee'))->with([
			'annee_scolaires' => AnneeScolaire::all()
		]);
	}

	public function update(FiliereRequest $request, Filiere $filiere)
	{
		$filePath = $request->hasFile('image') ? $this->updateFile($request, 'image', static::FOLDER, $filiere->getAttribute('image')) : $filiere->getAttribute('image');
		$anneeActiveId = AnneeScolaire::where('active', true)->first()->getAttribute('id');

		$filiere->update([
			...$request->except([
				'date_debut',
				'date_fin',
				'inscrits',
				'id'
			]),
			'image' => $filePath
		]);

		if ($anneeActiveId) {
			$exists = $filiere->anneesScolaires()
				->wherePivot('annee_scolaire_id', $anneeActiveId)
				->exists();

			if ($exists) {
				$filiere->anneesScolaires()->updateExistingPivot(
					$anneeActiveId,
					[
						'date_debut' => $request->date_debut,
						'date_fin'   => $request->date_fin,
					]
				);
			} else {
				$filiere->anneesScolaires()->attach(
					$anneeActiveId,
					[
						'date_debut' => $request->date_debut,
						'date_fin'   => $request->date_fin,
					]
				);
			}
		}

		return new FiliereResource($filiere);


		// return to_route('admin.filieres.index')->with(successMsg('Filière mise à jour avec succès.'));
	}

	// public function destroy(Request $request)
	// {
	// 	$filiere = $request->idfil;

	// 	$grades = Grade::query()->where('filiere_id', $filiere)->get();

	// 	if ($grades->isNotEmpty()) {
	// 		return back()->with(cannotDeleteItemMessage('cette filière'));
	// 	}
	// 	$fil = Filiere::query()->where('id', $filiere)->first();

	// 	if ($fil->image) {
	// 		$this->deleteFile($fil->image);
	// 	}

	// 	if ($fil->anneesScolaires()) {
	// 		$fil->anneesScolaires()->delete();
	// 	}
	// 	$fil->delete();

	// 	return new FiliereResource($filiere);

	// 	// return to_route('admin.filieres.index')->with(successMsg('Filière supprimée avec succès.'));
	// }

	public function destroy(Filiere $filiere)
	{


		$grades = Grade::query()->where('filiere_id', $filiere)->get();

		if ($grades->isNotEmpty()) {
			return response()->json([
				"message" => "Impossible de supprimer cette filiere"
			]);
		}

		if ($filiere->image) {
			$this->deleteFile($filiere->image);
		}
		$filiere->delete();

		return new FiliereResource($filiere);

		// return to_route('admin.filieres.index')->with(successMsg('Filière supprimée avec succès.'));
	}

	public function getProgramme(Filiere $filiere)
	{
		$programme = $filiere->load([
			'unitesEnseignements.periode',
			'unitesEnseignements.uniteDeValeurs'
		]);

		return response()->json([
			'data' => $programme
		]);
	}
}
