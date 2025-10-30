<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Mail\Admins\AdminWelcomeMail;
use App\Models\{Group, Role, Salle, UniteValeur as UV, User};
use App\Models\EmploiDuTemp;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
	 public function __construct()
	 {
	 	$this->authorizeResource(User::class, 'user');
	 }

	public function index(string $profil = 'tous-les-profils'): View
	{
		$data = [];
		$users = User::query()->with('roles')->get();
		if ($profil === 'tous-les-profils') {
			$data = [
				'title' => 'Page des membres de l\'administration',
				'breadcrumbs' => ['Administration', 'Membres de l\'administration', 'Liste'],
				'page_name' => 'Liste des membres de l\'administration'
			];
		}
		if ($profil === 'enseignants') {
			$users = User::enseignants()->get();
			$data = [
				'title' => 'Liste des enseignants',
				'breadcrumbs' => ['Administration', ['text' => 'Personnel', 'url' => route('admin.users.index')], 'Enseignants', 'Liste'],
				'page_name' => 'Liste des enseignants'
			];
		}
		return view('admin.users.index')->with([
			'users' => $users,
			'meta' => $data,
			'enseignants' => true
		]);
	}

	public function create(): View
	{
		return view('admin.users.create')->with([
			'user' => new User(),
			'roles' => Role::all()
		]);
	}

	// public function store(UserRequest $request): RedirectResponse
	// {
	// 	$user = User::create($request->all());
	// 	$user->roles()->attach($request->get('roles'));

	// 	Mail::to($user)->send(new AdminWelcomeMail($user, $request->get('clearPassword')));
	// 	// Todo Notifier l'utilisateur de la création de son compte
	// 	// Todo Envoyer un mail de confirmation de mot de passe à l'utilisateur
	// 	return to_route('admin.users.index')->with(successMsg('Utilisateur créé avec succès'));
	// }
	public function store(UserRequest $request): RedirectResponse
{
    $clearPassword = 'password'; 

    $data = $request->validated();

    $data['password'] = Hash::make($clearPassword);

    $user = User::create($data);

    $user->roles()->attach($request->get('roles'));

    Mail::to($user)->send(new AdminWelcomeMail($user, $clearPassword));

    return to_route('admin.users.index')->with(successMsg('Utilisateur créé avec succès'));
}

	public function edit(User $user): View
	{
		return view('admin.users.edit', compact('user'))->with([
			'roles' => Role::all()
		]);
	}

	public function update(Request $request, User $user): RedirectResponse
	{
		$request->validate([
			'nom' => ['required'],
			'prenom' => ['required'],
			'biographie' => ['nullable'],
			'genre' => ['required', 'string'],
			'email' => ['required', 'email',],
			'roles' => ['nullable', 'array', 'min:1'],
			'tel' => ['required'],
			'supervisor_type' => ['required', 'in:interne,externe,non_surveillant'],
			'supervisor_notes' => ['nullable', 'string']
		], [
			'nom.required' => "Le nom est requis",
			'prenom.required' => "Le prénom est requis",
			'genre.required' => "Le genre est requis",
			'email.required' => "L'adresse mail est requise",
			'roles.min' => "Veuillez choisir au moins un rôle",
			'tel.required' => "Le numéro de téléphone est requis",
			'supervisor_type.required' => "Le type de surveillant est requis",
			'supervisor_type.in' => "Type de surveillant invalide"
		], [
			'nom' => 'Le nom',
			'prenom' => 'Le prénom',
			'genre' => 'Le genre',
			'email' => 'L\'adresse mail',
			'roles' => 'Le rôle',
			'tel' => 'Le numero de téléphone',
			'supervisor_type' => 'Le type de surveillant',
			'supervisor_notes' => 'Les notes de surveillance'
		]);
		$user->update($request->all());
		$user->roles()->sync($request->get('roles'));
		if ($request->user()->getAttribute('id') === $user->getAttribute('id')) {
			// Todo rediriger sur la page de profil de l'utilisateur
//			return back()->with(successMsg('Profil modifié avec succès'));
		}
		return to_route('admin.users.index')->with(successMsg('Profil modifié avec succès'));
	}

	public function loadEmploiDuTemps(User $user): AnonymousResourceCollection
	{
		return EmploiDuTempsResource::collection($user->emploiDuTemps);
	}

	public function ShowEmploiDuTemps(User $user): View
	{
		//		dd(['resourceUrl' => route('admin.users.load-edt', $user)]);
		return view('admin.users.teacher-calendar.calendar', compact('user'))->with([
			'uvs' => Uv::all(),
			'types' => TypeProgrammeEnum::cases(),
			'groups' => Group::query()->with('filiere:code,id')->orderBy('nom')->get(),
			'salles' => Salle::query()->select(['nom', 'slug'])->get(),
			'resourceUrl' => route('admin.users.load-edt', $user),
			'meta' => [
				'title' => 'Emploi de ' . $user->getAttribute('genre')->name . ' ' . $user->getAttribute('nom') . ' ' . $user->getAttribute('prenom'),
				'page_name' => 'Emploi de ' . $user->getAttribute('genre')->name . ' ' . $user->getAttribute('nom') . ' ' . $user->getAttribute('prenom'),
				'breadcrumbs' => ['Administration', 'Gestion des emploi du temps', $user->getAttribute('nom') . ' ' . $user->getAttribute('prenom')]
			]
		]);
	}

	public function destroy(Request $request)
	{
		$userId = (int) $request->userId;
		$user = User::query()->find($userId);
		if (!$user)
			return back()->with(cannotDeleteItemMessage('La suppression n\'a pas pu se faire du faire de l\'inexistence en base de donnée de l\'élément choisi'));


		$user->delete();
		return to_route('admin.users.index')->with(successMsg('Élément supprimé avec succès'));

	}

	/**
	 * Retourne un récapitulatif des heures effectuées par chaque enseignant.
	 * Optionnel: date_debut et date_fin en query string pour filtrer la période.
	 */
	public function hoursSummary(Request $request): View
	{
		$start = $request->query('date_debut') ? Carbon::createFromFormat('Y-m-d', $request->query('date_debut'))->startOfDay() : null;
		$end = $request->query('date_fin') ? Carbon::createFromFormat('Y-m-d', $request->query('date_fin'))->endOfDay() : null;

		// Récupérer les enseignants
		$teachers = User::enseignants()->get();

		$summary = $teachers->map(function ($t) use ($start, $end) {
			$query = EmploiDuTemp::query()->where('owner_type', User::class)->where('owner_id', $t->id);
			if ($start) $query->where('debut', '>=', $start);
			if ($end) $query->where('fin', '<=', $end);
			$seconds = $query->get()->reduce(function ($carry, $item) {
				$carry += $item->debut->diffInSeconds($item->fin);
				return $carry;
			}, 0);

			$hours = round($seconds / 3600, 2);
			return [
				'user' => $t,
				'hours' => $hours,
				'seconds' => $seconds,
			];
		});

		return view('admin.users.hours-summary')->with([
			'summary' => $summary,
			'date_debut' => $request->query('date_debut'),
			'date_fin' => $request->query('date_fin'),
		]);
	}

	public function updateEmploiDuTemps(Request $request): RedirectResponse
	{

		dd($request->all());
		$request->validate([
			'userId' => ['required', 'integer', 'exists:users,id'],
			'create_date' => ['required', 'date'],
			'create_debut' => ['required', 'date_format:H:i'],
			'create_fin' => ['required', 'date_format:H:i', 'after:create_debut'],
			'create_grade_id' => ['required', 'exists:grades,slug'],
			'create_salle_id' => ['required', 'exists:salles,slug'],
			'create_uv_id' => ['required', 'exists:uvs,slug'],
			'create_type' => ['required', 'string'],
			'details' => ['nullable', 'string'],
		], [
			'create_fin.after' => 'L\'heure de fin doit être après l\'heure de début.',
		]);

		$startDateTime = Carbon::parse($request->create_date . ' ' . $request->create_debut);
		$endDateTime = Carbon::parse($request->create_date . ' ' . $request->create_fin);

		if ($endDateTime->isPast()) {
			return back()->withErrors(['create_fin' => 'Impossible de programmer un emploi du temps dans le passé.'])->withInput();
		}

		// Vérifier les chevauchements (exemple pour salle et groupe)
		$conflit = EmploiDuTemp::where('salle_id', $request->create_salle_id)
			->orWhere('group_id', $request->create_grade_id)
			->where(function ($query) use ($startDateTime, $endDateTime) {
				$query->whereBetween('debut', [$startDateTime, $endDateTime])
					->orWhereBetween('fin', [$startDateTime, $endDateTime])
					->orWhere(function ($query) use ($startDateTime, $endDateTime) {
						$query->where('debut', '<=', $startDateTime)
							->where('fin', '>=', $endDateTime);
					});
			})
			->exists();

		if ($conflit) {
			return back()->withErrors(['conflit' => 'Un emploi du temps existe déjà pour cette salle ou ce groupe à cette période.'])->withInput();
		}

		// Récupérer l’emploi du temps existant par ID ou slug (à adapter selon ton URL/clé)
		$emploi = EmploiDuTemp::findOrFail($request->emploi_id);

		// Mettre à jour
		$emploi->update([
			'debut' => $startDateTime,
			'fin' => $endDateTime,
			'group_id' => $request->group_id,
			'salle_id' => $request->salle_id,
			'uv_id' => $request->uv_id,
			'unite_enseignement_id' => null,
			'evenement_id' => null, 
			'type_programme' => $request->type_programme,
			'owner_type' => 'App\Models\User', 
			'owner_id' => $request->userId,
			'details' => $request->details,
			'annee_scolaire_id' => session('annee_scolaire_id'), // ou autre logique pour récupérer
		]);

		return to_route('admin.users.index')->with('success', 'Emploi du temps modifié avec succès.');
	}

}
