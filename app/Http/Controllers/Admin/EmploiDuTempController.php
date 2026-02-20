<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Exports\EmploiDuTempsMatriceExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Models\{EmploiDuTemp, Group, Salle, UniteValeur, User};
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class EmploiDuTempController extends Controller
{
	private function hasSalleOverlap(int $salleId, Carbon $debut, Carbon $fin, ?int $excludeId = null): bool
	{
		return EmploiDuTemp::query()
			->where('salle_id', $salleId)
			->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
			->where('debut', '<', $fin)
			->where('fin', '>', $debut)
			->exists();
	}

	// public function store(Request $request): Response|ResponseFactory|EmploiDuTempsResource
	// {


	// 	$rules = [
	// 		'debut' => ['required'],
	// 		'fin' => ['required'],
	// 		'date' => ['required', 'date'],
	// 		'uv_id' => ['required', 'exists:unite_valeurs,slug'],
	// 		'type' => ['required', Rule::enum(TypeProgrammeEnum::class)],
	// 		'grade' => ['required', 'exists:groups,slug'],
	// 		'salle' => ['required', 'exists:salles,slug'],
	// 		'teacher' => ['required', 'exists:users,slug'],
	// 		'details' => ['nullable'],
	// 		'evenement_id' => [
	// 			'nullable',
	// 			Rule::requiredIf(
	// 				fn() => $request->enum('type', TypeProgrammeEnum::class) === TypeProgrammeEnum::EVENEMENT
	// 			),
	// 			'exists:evenements,id'
	// 		],
	// 	];

	// 	$attributes = [
	// 		'debut' => 'La date ou l\'heure de début',
	// 		'fin' => 'La date ou l\'heure de fin',
	// 		'uv_id' => 'L\'unité de valeur',
	// 		'type' => 'Le type de programme',
	// 		'grade' => 'Le groupe d\'étudiants',
	// 		'salle' => 'La salle',
	// 		'teacher' => 'L\'enseignant',
	// 		'details' => 'Le champ détails',
	// 		'evenement_id' => 'L\'évènement',
	// 		'date' => 'La date de tenu de l\'enregistrement'
	// 	];

	// 	$validator = validator($request->all(), $rules, attributes: $attributes);

	// 	if ($validator->fails()) {
	// 		return __422($validator->errors()->first());
	// 	}

	// 	try {
	// 		$date = $request->get('date');

	// 		$request->merge([
	// 			'debut' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->get('debut')),
	// 			'fin' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->get('fin')),
	// 		]);

	// 		$salleId = Salle::query()->firstWhere('slug', $request->get('salle'))->getAttribute('id');
	// 		$groupId = Group::query()->firstWhere('slug', $request->get('grade'))->getAttribute('id');
	// 		$uvId = UniteValeur::query()->firstWhere('slug', $request->get('uv_id'))->getAttribute('id');
	// 		$owner_id = User::query()->firstWhere('slug', $request->get('teacher'))->getAttribute('id');
	// 		//			$evenementId = (int)Group::query()->firstWhere('slug', $request->get('evenement_id'))->getAttribute('id');

	// 		/*	dd([
	// 					  'debut' => $request->get('debut'),
	// 					  'fin' => $request->get('fin'),
	// 					  'uv_id' => $uvId,
	// 					  'type_programme' => $request->enum('type', TypeProgrammeEnum::class),
	// 					  'group_id' => $groupId,
	// 					  'salle_id' => $salleId,
	// 					  'owner_id' => $owner_id,
	// 					  'owner_type' => User::class,
	// 					  'details' => $request->get('details'),
	// 	  //				'evenement_id' => $evenementId,
	// 					  ...injectAnneeScolaireId()
	// 				  ]);*/


	// 		if ($request->get('fin') <= $request->get('debut')) {
	// 			return __422("L'heure de fin doit être postérieure à l'heure de début.");
	// 		}

	// 		// Blocage si la salle est occupée (chevauchement)
	// 		if ($this->hasSalleOverlap($salleId, $request->get('debut'), $request->get('fin'))) {
	// 			return __422('Impossible de programmer: la salle est déjà occupée sur cette plage horaire.');
	// 		}

	// 		$emploiDuTemps = EmploiDuTemp::create([
	// 			'debut' => $request->get('debut'),
	// 			'fin' => $request->get('fin'),
	// 			'uv_id' => $uvId,
	// 			'type_programme' => $request->enum('type', TypeProgrammeEnum::class),
	// 			'group_id' => $groupId,
	// 			'salle_id' => $salleId,
	// 			'owner_id' => $owner_id,
	// 			'owner_type' => User::class,
	// 			'details' => $request->get('details'),
	// 			//				'evenement_id' => $evenementId,
	// 			...injectAnneeScolaireId()
	// 		]);

	// 	} catch (Throwable $e) {
	// 		return __500($e->getMessage());
	// 	}

	// 	return new EmploiDuTempsResource($emploiDuTemps);
	// }
	public function store(Request $request): Response|ResponseFactory|EmploiDuTempsResource
	{
		$rules = [
			// CORRECTION: Acceptez les dates complètes directement
			'debut' => ['required', 'date_format:Y-m-d H:i:s'],
			'fin' => ['required', 'date_format:Y-m-d H:i:s'],

			// Vous n'avez plus besoin du champ 'date' séparé
			'uv_id' => ['required'],
			'type' => ['required', Rule::enum(TypeProgrammeEnum::class)],
			'grade' => ['required'],
			'salle' => ['required'],
			'teacher' => ['required'],
			'details' => ['nullable'],

			// RÉCURRENCE
			'recurrence_type' => ['nullable', Rule::in(['aucune', 'hebdomadaire', 'quotidienne'])],
			'recurrence_days' => ['nullable', 'array'],
			'recurrence_end_date' => ['nullable', 'date', 'after:debut'],

			'evenement_id' => [
				'nullable',
				Rule::requiredIf(
					fn() => $request->enum('type', TypeProgrammeEnum::class) === TypeProgrammeEnum::EVENEMENT
				),
				'exists:evenements,id'
			],
		];

		$validator = validator($request->all(), $rules);
		if ($validator->fails()) {
			return __422($validator->errors()->first());
		}

		try {
			$debut = Carbon::parse($request->debut);
			$fin = Carbon::parse($request->fin);

			if ($fin->lessThanOrEqualTo($debut)) {
				return __422("L'heure de fin doit être postérieure à l'heure de début.");
			}

			$salleId = Salle::where('slug', $request->salle)
				->orWhere('id', intval($request->salle))
				->value('id');
			if (!$salleId) return __422('Salle invalide.');

			$groupId = Group::where('slug', $request->grade)
				->orWhere('id', intval($request->grade))
				->value('id');
			if (!$groupId) return __422('Groupe invalide.');

			$uvId = UniteValeur::where('slug', $request->uv_id)
				->orWhere('id', intval($request->uv_id))
				->value('id');
			if (!$uvId) return __422('Unité de valeur invalide.');

			$ownerId = User::where('slug', $request->teacher)
				->orWhere('id', intval($request->teacher))
				->value('id');
			if (!$ownerId) return __422('Enseignant invalide.');

			if ($this->hasSalleOverlap($salleId, $debut, $fin)) {
				return __422('Salle déjà occupée sur cette plage horaire.');
			}

			$recurrenceType = $request->input('recurrence_type', 'aucune');
			$recurrenceDays = null;
			if (in_array($recurrenceType, ['hebdomadaire', 'quotidienne'])) {
				$recurrenceDays = implode(',', $request->input('recurrence_days', []));
			}

			$emploiDuTemps = EmploiDuTemp::create([
				'debut' => $debut,
				'fin' => $fin,
				'uv_id' => $uvId,
				'type_programme' => $request->enum('type', TypeProgrammeEnum::class),
				'group_id' => $groupId,
				'salle_id' => $salleId,
				'owner_id' => $ownerId,
				'owner_type' => User::class,
				'details' => $request->details,
				'recurrence_type' => $recurrenceType,
				'recurrence_days' => $recurrenceDays,
				'recurrence_end_date' => $request->recurrence_end_date,
				'evenement_id' => $request->evenement_id,
				...injectAnneeScolaireId(),
			]);
		} catch (Throwable $e) {
			return __500($e->getMessage());
		}

		return new EmploiDuTempsResource($emploiDuTemps);
	}


	// public function destroy(Request $request): Response|ResponseFactory
	// {
	// 	$slug = $request->str('slug');
	// 	$edt = EmploiDuTemp::query()->where('slug', $slug);
	// 	if (!$edt)
	// 		return __404('La suppression n\'a pas pu se faire du faire de l\'inexistence en base de donnée de l\'élément choisi');

	// 	$edt->delete();
	// 	return __200('Élément supprimé avec succès');
	// }
	public function destroy(string $slug): Response|ResponseFactory
	{
		$edt = EmploiDuTemp::where('slug', $slug)->first();

		if (!$edt) {
			return __404(
				"La suppression n'a pas pu se faire : élément introuvable."
			);
		}

		$edt->delete();

		return __200('Élément supprimé avec succès');
	}


	// public function updateDates(Request $request): Response|ResponseFactory
	// {
	// 	$rules = [
	// 		'debut' => ['required',],
	// 		'fin' => ['required',],
	// 	];

	// 	$attributes = [
	// 		'debut' => 'La date ou l\'heure de début',
	// 		'fin' => 'La date ou l\'heure de fin',
	// 	];

	// 	$validator = validator($request->all(), $rules, [], $attributes);

	// 	if ($validator->fails()) {
	// 		return __422($validator->errors()->first());
	// 	}

	// 	try {
	// 		$debut = Carbon::createFromFormat('Y-m-d\TH:i:s', $request->input('debut'));
	// 		$fin = Carbon::createFromFormat('Y-m-d\TH:i:s', $request->input('fin'));
	// 	} catch (Throwable) {
	// 		return __500('Le format de la nouvelle date n\'est pas valable');
	// 	}

	// 	$edt = EmploiDuTemp::query()->firstWhere('slug', $request->input('slug'));

	// 	if (!$edt) {
	// 		return __404('La modification n\'a pas pu se faire : élément introuvable.');
	// 	}

	// 	if ($edt->fin->isBefore(now())) {
	// 		return __422('Impossible de modifier : l\'événement est déjà terminé.');
	// 	}

	// 	if ($fin->lessThanOrEqualTo($debut)) {
	// 		return __422("L'heure de fin doit être postérieure à l'heure de début.");
	// 	}

	// 	// Vérifier les chevauchements pour la même salle en excluant l'événement courant
	// 	if ($this->hasSalleOverlap((int)$edt->getAttribute('salle_id'), $debut, $fin, (int)$edt->getAttribute('id'))) {
	// 		return __422('Impossible de déplacer : la salle est déjà occupée sur cette plage horaire.');
	// 	}

	// 	try {
	// 		$edt->update(compact('debut', 'fin'));
	// 		Log::info('edt', [$edt]);
	// 		Log::info('values', compact('debut', 'fin'));
	// 	} catch (Throwable) {
	// 		return __500();
	// 	}

	// 	return __200('Élément modifié avec succès');
	// }
	public function updateDates(Request $request): Response|ResponseFactory
	{
		$rules = [
			'slug' => ['required', 'exists:emploi_du_temps,slug'],
			'debut' => ['required'],
			'fin' => ['required'],
		];

		$validator = validator($request->all(), $rules);
		if ($validator->fails()) return __422($validator->errors()->first());

		$edt = EmploiDuTemp::whereSlug($request->slug)->first();
		if (!$edt) return __404();

		if ($edt->recurrence_type !== 'aucune') {
			return __422(
				'Impossible de déplacer une programmation récurrente. Modifiez-la globalement.'
			);
		}

		try {
			$debut = Carbon::createFromFormat('Y-m-d\TH:i:s', $request->debut);
			$fin = Carbon::createFromFormat('Y-m-d\TH:i:s', $request->fin);
		} catch (Throwable) {
			return __500('Format de date invalide');
		}

		if ($fin->lessThanOrEqualTo($debut)) {
			return __422("L'heure de fin doit être postérieure à l'heure de début.");
		}

		if ($this->hasSalleOverlap(
			$edt->salle_id,
			$debut,
			$fin,
			$edt->id
		)) {
			return __422('Salle déjà occupée sur cette plage horaire.');
		}

		$edt->update(compact('debut', 'fin'));

		return __200('Programmation modifiée avec succès');
	}


	/**
	 * Mise à jour d'une programmation (changement de salle par groupe, mise à jour des horaires, etc.)
	 */
	// public function update(Request $request): Response|ResponseFactory|EmploiDuTempsResource
	// {
	// 	$rules = [
	// 		'slug' => ['required', 'exists:emploi_du_temps,slug'],
	// 		'salle' => ['nullable', 'exists:salles,slug'],
	// 		'date' => ['nullable', 'date'],
	// 		'debut' => ['nullable'],
	// 		'fin' => ['nullable'],
	// 		'uv_id' => ['nullable', 'exists:unite_valeurs,slug'],
	// 		'type' => ['nullable', Rule::enum(TypeProgrammeEnum::class)],
	// 		'grade' => ['nullable', 'exists:groups,slug'],
	// 		'teacher' => ['nullable', 'exists:users,slug'],
	// 		'details' => ['nullable'],
	// 	];

	// 	$validator = validator($request->all(), $rules);
	// 	if ($validator->fails()) {
	// 		return __422($validator->errors()->first());
	// 	}

	// 	$edt = EmploiDuTemp::query()->firstWhere('slug', $request->get('slug'));
	// 	if (!$edt) return __404();

	// 	$payload = [];
	// 	// Compute datetime if provided
	// 	if ($request->filled('date') && $request->filled('debut') && $request->filled('fin')) {
	// 		try {
	// 			$payload['debut'] = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('debut'));
	// 			$payload['fin'] = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('fin'));
	// 		} catch (Throwable) {
	// 			return __500('Format de date/heure invalide');
	// 		}
	// 		if ($payload['fin']->lessThanOrEqualTo($payload['debut'])) {
	// 			return __422("L'heure de fin doit être postérieure à l'heure de début.");
	// 		}
	// 	}

	// 	if ($request->filled('salle')) {
	// 		$payload['salle_id'] = Salle::query()->firstWhere('slug', $request->get('salle'))?->getAttribute('id');
	// 	}
	// 	if ($request->filled('grade')) {
	// 		$payload['group_id'] = Group::query()->firstWhere('slug', $request->get('grade'))?->getAttribute('id');
	// 	}
	// 	if ($request->filled('uv_id')) {
	// 		$payload['uv_id'] = UniteValeur::query()->firstWhere('slug', $request->get('uv_id'))?->getAttribute('id');
	// 	}
	// 	if ($request->filled('teacher')) {
	// 		$payload['owner_id'] = User::query()->firstWhere('slug', $request->get('teacher'))?->getAttribute('id');
	// 		$payload['owner_type'] = User::class;
	// 	}
	// 	if ($request->filled('type')) {
	// 		$payload['type_programme'] = $request->enum('type', TypeProgrammeEnum::class);
	// 	}
	// 	if ($request->filled('details')) {
	// 		$payload['details'] = $request->get('details');
	// 	}

	// 	// If salle/time provided, check overlap on target salle
	// 	$salleIdToCheck = $payload['salle_id'] ?? (int)$edt->getAttribute('salle_id');
	// 	$debutToCheck = $payload['debut'] ?? $edt->getAttribute('debut');
	// 	$finToCheck = $payload['fin'] ?? $edt->getAttribute('fin');

	// 	if ($this->hasSalleOverlap($salleIdToCheck, $debutToCheck, $finToCheck, (int)$edt->getAttribute('id'))) {
	// 		return __422('Impossible de modifier : la salle est déjà occupée sur cette plage horaire.');
	// 	}

	// 	try {
	// 		$edt->update($payload);
	// 	} catch (Throwable $e) {
	// 		return __500($e->getMessage());
	// 	}

	// 	return new EmploiDuTempsResource($edt->fresh());
	// }
	public function update(Request $request): Response|ResponseFactory|EmploiDuTempsResource
	{
		$rules = [
			'slug' => ['required', 'exists:emploi_du_temps,slug'],

			// AJOUTER LES RÈGLES POUR DEBUT ET FIN
			'debut' => ['nullable', 'date'],
			'fin' => ['nullable', 'date'],

			// On valide la présence uniquement (ID ou slug)
			'salle' => ['nullable'],
			'uv_id' => ['nullable'],
			'grade' => ['nullable'],
			'teacher' => ['nullable'],

			'type' => ['nullable', Rule::enum(TypeProgrammeEnum::class)],
			'details' => ['nullable'],

			// RÉCURRENCE
			'recurrence_type' => ['nullable', Rule::in(['aucune', 'hebdomadaire', 'quotidienne'])],
			'recurrence_days' => ['nullable', 'array'],
			'recurrence_end_date' => ['nullable', 'date'],
		];

		$validator = validator($request->all(), $rules);
		if ($validator->fails()) {
			return __422($validator->errors()->first());
		}

		$edt = EmploiDuTemp::where('slug', $request->slug)->first();
		if (!$edt) {
			return __404('Programme introuvable.');
		}

		$payload = [];

		// AJOUTER LE TRAITEMENT POUR DEBUT ET FIN
		if ($request->filled('debut')) {
			$payload['debut'] = $request->debut;
		}

		if ($request->filled('fin')) {
			$payload['fin'] = $request->fin;
		}

		if ($request->filled('salle')) {
			$salleId = Salle::where('slug', $request->salle)
				->orWhere('id', intval($request->salle))
				->value('id');

			if (!$salleId) return __422('Salle invalide.');

			$payload['salle_id'] = $salleId;
		}

		if ($request->filled('uv_id')) {
			$uvId = UniteValeur::where('slug', $request->uv_id)
				->orWhere('id', intval($request->uv_id))
				->value('id');

			if (!$uvId) return __422('Unité de valeur invalide.');

			$payload['uv_id'] = $uvId;
		}

		if ($request->filled('grade')) {
			$groupId = Group::where('slug', $request->grade)
				->orWhere('id', intval($request->grade))
				->value('id');

			if (!$groupId) return __422('Groupe invalide.');

			$payload['group_id'] = $groupId;
		}

		if ($request->filled('teacher')) {
			$ownerId = User::where('slug', $request->teacher)
				->orWhere('id', intval($request->teacher))
				->value('id');

			if (!$ownerId) return __422('Enseignant invalide.');

			$payload['owner_id'] = $ownerId;
			$payload['owner_type'] = User::class;
		}

		// TYPE
		if ($request->filled('type')) {
			$payload['type_programme'] = $request->enum('type', TypeProgrammeEnum::class);
		}

		// DÉTAILS
		if ($request->has('details')) {
			$payload['details'] = $request->details;
		}

		// RÉCURRENCE
		if ($request->filled('recurrence_type')) {
			$payload['recurrence_type'] = $request->recurrence_type;

			if (in_array($request->recurrence_type, ['hebdomadaire', 'quotidienne'])) {
				$payload['recurrence_days'] = implode(',', $request->recurrence_days ?? []);
				$payload['recurrence_end_date'] = $request->recurrence_end_date;
			} else {
				// aucune
				$payload['recurrence_days'] = null;
				$payload['recurrence_end_date'] = null;
			}
		}

		// CORRECTION : Convertir les dates en Carbon pour la vérification
		$debutToCheck = isset($payload['debut'])
			? Carbon::parse($payload['debut'])
			: $edt->debut;

		$finToCheck = isset($payload['fin'])
			? Carbon::parse($payload['fin'])
			: $edt->fin;

		$salleIdToCheck = $payload['salle_id'] ?? $edt->salle_id;

		if ($this->hasSalleOverlap(
			$salleIdToCheck,
			$debutToCheck,  // Maintenant un objet Carbon
			$finToCheck,    // Maintenant un objet Carbon
			$edt->id
		)) {
			return __422('Salle déjà occupée sur cette plage horaire.');
		}

		$edt->update($payload);

		return new EmploiDuTempsResource($edt->fresh());
	}


	/**
	 * Consultation de disponibilité d'une salle sur une plage [date + début, fin]
	 */
	// public function checkAvailability(Request $request): Response|ResponseFactory
	// {
	// 	$rules = [
	// 		'salle' => ['required', 'exists:salles,slug'],
	// 		'date' => ['required', 'date'],
	// 		'debut' => ['required'],
	// 		'fin' => ['required'],
	// 	];
	// 	$validator = validator($request->all(), $rules);
	// 	if ($validator->fails()) return __422($validator->errors()->first());

	// 	try {
	// 		$debut = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('debut'));
	// 		$fin = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('fin'));
	// 	} catch (Throwable) {
	// 		return __500('Format de date/heure invalide');
	// 	}

	// 	if ($fin->lessThanOrEqualTo($debut)) return __422("L'heure de fin doit être postérieure à l'heure de début.");

	// 	$salleId = Salle::query()->firstWhere('slug', $request->get('salle'))?->getAttribute('id');
	// 	$occupied = $this->hasSalleOverlap((int)$salleId, $debut, $fin);

	// 	return response([
	// 		'available' => !$occupied,
	// 		'message' => $occupied ? 'Salle occupée sur cette plage horaire.' : 'Salle disponible.'
	// 	]);
	// }

	public function checkAvailability(Request $request): Response|ResponseFactory
	{
		$rules = [
			'salle' => ['required'],
			'date' => ['required', 'date'],
			'debut' => ['required'],
			'fin' => ['required'],
		];

		$validator = validator($request->all(), $rules);
		if ($validator->fails()) return __422($validator->errors()->first());

		try {
			$debut = Carbon::createFromFormat(
				'Y-m-d H:i',
				$request->date . ' ' . $request->debut
			);
			$fin = Carbon::createFromFormat(
				'Y-m-d H:i',
				$request->date . ' ' . $request->fin
			);
		} catch (Throwable) {
			return __500('Format de date invalide');
		}

		if ($fin->lessThanOrEqualTo($debut)) {
			return __422("L'heure de fin doit être postérieure à l'heure de début.");
		}

		$salleId = Salle::where('slug', $request->salle)->orWhere('id', $request->salle)->first()->getAttribute('id');

		$occupied = $this->hasSalleOverlap($salleId, $debut, $fin);

		return response([
			'available' => !$occupied,
			'message' => $occupied
				? 'Salle occupée sur cette plage horaire.'
				: 'Salle disponible.'
		]);
	}


	public function setEmploiDuTempsForUser(): void {}


	public function exportMatrice(Request $request)
	{
		$request->validate([
			'group_id' => 'required|exists:groups,id',
			'semaine' => 'nullable|date',
		]);

		$group_id = $request->group_id;
		$date_ref = $request->semaine ? Carbon::parse($request->semaine) : Carbon::now();

		// Une semaine va du lundi au dimanche
		$date_debut = $date_ref->copy()->startOfWeek(Carbon::MONDAY);
		$date_fin = $date_ref->copy()->endOfWeek(Carbon::SUNDAY);

		$groupe = Group::find($group_id);
		$filename = 'emploi_du_temps_' . $groupe->nom . '_' .
			$date_debut->format('d-m-Y') . '.xlsx';

		return Excel::download(
			new EmploiDuTempsMatriceExport($group_id, $date_debut, $date_fin),
			$filename
		);
	}
}
