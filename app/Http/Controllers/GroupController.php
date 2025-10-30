<?php

namespace App\Http\Controllers;

use App\Enums\TypeProgrammeEnum;
use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Http\Resources\Admin\UniteValeurPartialRessource;
use App\Jobs\CreatingUserBasedOnCandidatsDataJob;
use App\Models\{Candidature, Etudiant, Filiere, Group, Salle, UniteValeur as UV};
use App\Models\EtudiantGroup;
use App\Models\RoleUser;
use App\Models\Periode;
use App\Models\AnneeScolaire;
use App\Models\Niveau;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Http\{RedirectResponse, Request, Resources\Json\AnonymousResourceCollection, Response};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{

	public function index(): View
	{
		return view('admin.groups.index')->with([
			'groups' => Group::with('filiere')->withCount('etudiants')->get(),
			'filieres' => Filiere::all(),
		]);
	}

	public function store(Request $request): RedirectResponse
	{
		// Récupération du groupe dans l'URL
		// $groupId = $request->route('group', new Group())->getAttribute('id');

		if (!$request->groupId) {
			
			$groupId = Group::query()->where('nom', $request->nom)->where('filiere_id', $request->filiere_id)->first();
			$groupId = $groupId ? (int) $groupId->getAttribute('id') : null;
		} else {
			$groupId = (int) $request->groupId;
		}
		// $groupId =  Group::query()->where('nom', $groupId)->first()->getAttribute('id');

		$request->merge(['nom' => Str::upper($request->get('nom'))]);

		$request->validate([
			'nom' => [
				'required',
				Rule::unique('groups')->ignore($groupId)
					// Les deux closes suivantes permettent d'assurer l'unicité des groupes par filières : ASR ne peux pas avoir 2 groupes A
					->where(function ($builder) use ($request) {
						/** * Cette section permet de supporter l'auto-completion de l'IDE * @var Builder $builder */
						return $builder->where('groups.filiere_id', $request->get('grade_id'));
					})
					->where('nom', $request->get('nom')),
				'filiere_id' => ['required', 'exists:filieres,id']
			],
			[
				'nom.required' => 'Le nom du groupe est obligatoire',
				'filiere_id.required' => 'La filière est obligatoire',
				'nom.unique' => 'Un groupe portant le même nom pour la même filière existe déjà',
			]
		]);

		$group = Group::query()->where('nom', $request->get('nom'))
			->where('filiere_id', $request->get('filiere_id'))->first();


		if ($group || $request->groupId) {
			$group = Group::query()->where('id', $request->get('groupId'))->first();

			$group->update($request->only(['nom', 'filiere_id']));
		} else {
			Group::firstOrCreate([
				...$request->only(['nom', 'filiere_id']),
				...injectAnneeScolaireId()
			]);
		}

		return to_route('admin.groups.index')->with(successMsg('Groupe créé/modifié avec succès'));
	}
	
	public function destroy(Request $request): RedirectResponse
	{
		$groupe = $request->groupe;

		if (EtudiantGroup::query()->where('group_id', $groupe)->exists()) {
			return to_route('admin.groups.index')->with(errorMsg('Impossible de supprimer le groupe, il contient des étudiants'));
		}

		Group::query()->where('id', $groupe)->first()->delete();

		return to_route('admin.groups.index')->with(successMsg('Groupe supprimé avec succès'));
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

	public function storeGroupAssignment(Request $request, Group $group): RedirectResponse
	{
		CreatingUserBasedOnCandidatsDataJob::dispatch(
			$request->collect('candidats'),
			(int) $group->getAttribute('id')
		);

		successMsg('Opération effectué avec succès. Patientez quelques instants pour l\'exécution des tâches en arrière plan.');
		return to_route('admin.groups.index');
	}

	public function loadCalendar(Group $group): AnonymousResourceCollection
{
    try {
        $emplois = $group->emploiDuTemps()
            ->with([
                'group.filiere',
                'salle',
                'uv',
                'owner',
                'evenement' => function($query) {
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
        \Log::error('Error loading calendar: '.$e->getMessage());
        // Pour respecter le type de retour, lancez une exception qui sera convertie en réponse JSON par Laravel
        throw new \RuntimeException('Impossible de charger le calendrier');
    }
}
	public function getEtudiants(Group $group): View
	{

		$anneeActive=AnneeScolaire::where('active',true)->first();

	$anneeActive=collect($anneeActive);
		$periodes=Periode::where('annee_scolaire_id',$anneeActive['id'])->get();

	


		return view('admin.etudiants.index', compact('group'))->with([
			'etudiants' => $group->etudiants,
			'niveaux'=>Niveau::all(),
			"periodes"=>$periodes
			,
			'groups' => Group::where('filiere_id',$group->filiere_id)
				->withCount('etudiants')
				->get(),
			'meta' => [
				''
			]
		]);
	}

	public function displayCalendar(Group $group): View
	{
		return view('admin.groups.calendar', compact('group'))->with([
			'uvs' => $group->matieres(),
			'types' => TypeProgrammeEnum::cases(),
			'salles' => Salle::query()->select(['nom', 'slug'])->orderBy('nom')->get(),
			// 'teachers' => $group->matieresQueryBuilder()->with('enseignant:id,nom,prenom,slug')->get()->map(fn(Uv $uniteValeur) => $uniteValeur->enseignant)->unique(),
			'resourceUrl' => route('admin.groups.load-calendar', $group)
		]);
	}

	public function getMatieres(Group $group): AnonymousResourceCollection
	{
		return UniteValeurPartialRessource::collection($group->matieres());
	}
}






