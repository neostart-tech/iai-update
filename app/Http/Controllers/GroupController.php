<?php

namespace App\Http\Controllers;

use App\Enums\TypeProgrammeEnum;
use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Http\Resources\Admin\UniteValeurPartialRessource;
use App\Http\Resources\EtudiantRessource;
use App\Http\Resources\GroupeResource;
use App\Http\Resources\NiveauResource;
use App\Http\Resources\PeriodeResource;
use App\Http\Resources\SalleResource;
use App\Http\Resources\UvResource;
use App\Jobs\CreatingUserBasedOnCandidatsDataJob;
use App\Models\{Candidature, Etudiant, EtudiantNiveau, Filiere, Group, Salle, UniteValeur as UV};
use App\Models\EtudiantGroup;
use App\Models\RoleUser;
use App\Models\Periode;
use App\Models\AnneeScolaire;
use App\Models\Niveau;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Http\{RedirectResponse, Request, Resources\Json\AnonymousResourceCollection, Response};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{

	public function index()
	{
		// $anneeActive = AnneeScolaire::where('active', true)->first()->getAttribute('id');
		// return view('admin.groups.index')->with([
		// 	'groups' => Group::with(['filieres', 'niveau'])
		// 		->withCount(['etudiants'=>function($query) use ($anneeActive){
		// 			$query->where('etudiant_group.annee_scolaire_id',$anneeActive);
		// 		}])
		// 		->get(),
		// 	'niveaux' => Niveau::all(),
		// 	'filieres' => Filiere::all(),
		// ]);
		$anneeActive = injectAnneeScolaireId();

		$groups = Group::with(['filieres', 'niveau'])
			->withCount([
				'etudiants' => function ($query) use ($anneeActive) {
					$query->where('etudiant_group.annee_scolaire_id', $anneeActive);
				}
			])
			->get();

		return GroupeResource::collection($groups);
	}

	public function store(Request $request)
	{
		// Récupération du groupe dans l'URL
		// $groupId = $request->route('group', new Group())->getAttribute('id');

		if (!$request->groupId) {

			$groupId = Group::query()->where('nom', $request->nom)->where('niveau_id', $request->niveau_id)->first();
			$groupId = $groupId ? (int) $groupId->getAttribute('id') : null;
		} else {
			$groupId = (int) $request->groupId;
		}
		// $groupId =  Group::query()->where('nom', $groupId)->first()->getAttribute('id');

		$request->merge(['nom' => Str::upper($request->get('nom'))]);
		$message = '';
		$group = null;

		$request->validate([
			'nom' => ['required'],
			'niveau_id' => ['required', 'exists:niveaux,id'],
			'filieres' => ['required', 'array', 'min:1'],
			'filieres.*' => ['exists:filieres,id'],
		], [
			'nom.required' => 'Le nom du groupe est obligatoire',
			'niveau_id.required' => 'Le niveau est obligatoire',
			'filieres.required' => 'Au moins une filière est requise',
		]);


		DB::transaction(function () use ($request, &$message, &$group) {

			if ($request->groupId) {
				// UPDATE
				$group = Group::findOrFail($request->groupId);

				$group->update([
					'nom' => $request->nom,
					'niveau_id' => $request->niveau_id,
				]);

				$group->filieres()->sync($request->filieres);

				$message = "Groupe modifié avec succès";
			} else {
				$group = Group::create([
					'nom' => $request->nom,
					'niveau_id' => $request->niveau_id,
					...injectAnneeScolaireId()
				]);

				$group->filieres()->attach($request->filieres);

				$message = "Groupe créé avec succès";
			}
		});
		return new GroupeResource($group);
		// return to_route('admin.groups.index')->with(successMsg($message));
	}

	public function destroy(Group $groupe)
	{

		if ($groupe->etudiants()->exists()) {
			return __422(
				'Impossible de supprimer le groupe, il contient des étudiants'
			);
		}
		$groupe->filieres()->detach();

		$groupe->delete();
		// return new GroupeResource($groupe);
		// return back()->with(successMsg('Groupe supprimé avec succès'));
	}

	// #[NoReturn]
	public function showGroupAssignmentView(Group $group): View
	{
		dd('something went wrong bad here 😪');
		/*return view('admin.groups.assignment', compact('group'))->with([
										  'candidatures' => Etudiant::query()
											  ->whereNotIn('id')
											  ->orderBy('nom')
											  ->orderBy('prenom')
											  ->get()
									  ]);*/
	}

	public function storeGroupAssignment(Request $request, Group $group)
	{
		CreatingUserBasedOnCandidatsDataJob::dispatch(
			$request->collect('candidats'),
			(int) $group->getAttribute('id')
		);

		// successMsg('Opération effectué avec succès. Patientez quelques instants pour l\'exécution des tâches en arrière plan.');
		// return to_route('admin.groups.index');
		return new GroupeResource($group);
	}

	public function loadCalendar(Group $group): AnonymousResourceCollection
	{
		try {
			$emplois = $group->emploiDuTemps()
				->with([
					'group.niveau',
					'salle',
					'uv',
					'owner',
					'evenement' => function ($query) {
						$query->select('id', 'name'); // Sélectionnez uniquement les champs nécessaires
					}
				])
				->get();

			if ($emplois->isEmpty()) {
				// Retournez une collection vide plutôt qu'une réponse JSON
				return EmploiDuTempsResource::collection(collect());
			}

			return EmploiDuTempsResource::collection($emplois);
		} catch (\Exception $e) {
			// Log::error('Error loading calendar: ' . $e->getMessage());
			__422($e);
			// Pour respecter le type de retour, lancez une exception qui sera convertie en réponse JSON par Laravel
			throw new \RuntimeException('Impossible de charger le calendrier');
		}
	}
	public function getEtudiants(Group $group)
	{
		$anneeActive = AnneeScolaire::where('active', true)->first()->getAttribute('id');

		// dd(count(EtudiantGroup::where('annee_scolaire_id',$anneeActive->id)->where('group_id',$group->id)->get()));

		$etudiants = Etudiant::whereHas('groups', function ($query) use ($group, $anneeActive) {
			$query->where('etudiant_group.group_id', $group->id)->where('etudiant_group.annee_scolaire_id', $anneeActive);
		})->get();



		// $anneeActive = collect($anneeActive->pluck('id'));
		$periodes = Periode::where('annee_scolaire_id', $anneeActive)->get();

		$groups = Group::withCount('etudiants')->get();

		return view('admin.etudiants.index', compact('group'))->with([
			'etudiants' => $etudiants,
			'niveaux' => Niveau::all(),
			"periodes" => $periodes,
			'groups' => Group::withCount('etudiants')
				->get(),
			'meta' => [
				''
			]
		]);
		// return response()->json([
		// 	'data' => EtudiantRessource::collection($etudiants),
		// ]);
	}

	public function displayCalendar(Group $group)
	{
		return EmploiDuTempsResource::collection($group->emploiDuTemps);

		return view('admin.groups.calendar', compact('group'))->with([
			'uvs' => $group->matieres(),
			'types' => TypeProgrammeEnum::cases(),
			'salles' => Salle::query()
				->select(['nom', 'slug'])
				->orderBy('nom')
				->get(),
			'resourceUrl' => route('admin.groups.load-calendar', $group),
		]);
	}

	// public function displayCalendar(Group $group)
	// {
	// 	$group->load([
	// 		'matieres.enseignant:id,nom,prenom',
	// 	]);

	// 	return response()->json([
	// 		'data' => [
	// 			'group' => new GroupeResource($group),
	// 			'uvs' => UvResource::collection($group->matieres),
	// 		],

	// 		'meta' => [
	// 			'types' => collect(TypeProgrammeEnum::cases())
	// 				->map(fn($type) => [
	// 					'name' => $type->name,
	// 					'value' => $type->value,
	// 				]),

	// 			'salles' => SalleResource::collection(
	// 				Salle::select('nom', 'slug')->orderBy('nom')->get()
	// 			),
	// 		],
	// 	]);
	// }

	public function getMatieres(Group $group): AnonymousResourceCollection
	{
		return UniteValeurPartialRessource::collection($group->matieres());
	}
}
