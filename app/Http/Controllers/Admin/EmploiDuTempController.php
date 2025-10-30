<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Models\{EmploiDuTemp, Group, Salle, UniteValeur, User};
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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

	public function store(Request $request): Response|ResponseFactory|EmploiDuTempsResource
	{


		$rules = [
			'debut' => ['required'],
			'fin' => ['required'],
			'date' => ['required', 'date'],
			'uv_id' => ['required', 'exists:unite_valeurs,slug'],
			'type' => ['required', Rule::enum(TypeProgrammeEnum::class)],
			'grade' => ['required', 'exists:groups,slug'],
			'salle' => ['required', 'exists:salles,slug'],
			'teacher' => ['required', 'exists:users,slug'],
			'details' => ['nullable'],
			'evenement_id' => [
				'nullable',
				Rule::requiredIf(
					fn() => $request->enum('type', TypeProgrammeEnum::class) === TypeProgrammeEnum::EVENEMENT
				),
				'exists:evenements,id'
			],
		];

		$attributes = [
			'debut' => 'La date ou l\'heure de début',
			'fin' => 'La date ou l\'heure de fin',
			'uv_id' => 'L\'unité de valeur',
			'type' => 'Le type de programme',
			'grade' => 'Le groupe d\'étudiants',
			'salle' => 'La salle',
			'teacher' => 'L\'enseignant',
			'details' => 'Le champ détails',
			'evenement_id' => 'L\'évènement',
			'date' => 'La date de tenu de l\'enregistrement'
		];

		$validator = validator($request->all(), $rules, attributes: $attributes);

		if ($validator->fails()) {
			return __422($validator->errors()->first());
		}

		try {
			$date = $request->get('date');

			$request->merge([
				'debut' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->get('debut')),
				'fin' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->get('fin')),
			]);

			$salleId = Salle::query()->firstWhere('slug', $request->get('salle'))->getAttribute('id');
			$groupId = Group::query()->firstWhere('slug', $request->get('grade'))->getAttribute('id');
			$uvId = UniteValeur::query()->firstWhere('slug', $request->get('uv_id'))->getAttribute('id');
			$owner_id = User::query()->firstWhere('slug', $request->get('teacher'))->getAttribute('id');
			//			$evenementId = (int)Group::query()->firstWhere('slug', $request->get('evenement_id'))->getAttribute('id');

			/*	dd([
						  'debut' => $request->get('debut'),
						  'fin' => $request->get('fin'),
						  'uv_id' => $uvId,
						  'type_programme' => $request->enum('type', TypeProgrammeEnum::class),
						  'group_id' => $groupId,
						  'salle_id' => $salleId,
						  'owner_id' => $owner_id,
						  'owner_type' => User::class,
						  'details' => $request->get('details'),
		  //				'evenement_id' => $evenementId,
						  ...injectAnneeScolaireId()
					  ]);*/


			if ($request->get('fin') <= $request->get('debut')) {
				return __422("L'heure de fin doit être postérieure à l'heure de début.");
			}

			// Blocage si la salle est occupée (chevauchement)
			if ($this->hasSalleOverlap($salleId, $request->get('debut'), $request->get('fin'))) {
				return __422('Impossible de programmer: la salle est déjà occupée sur cette plage horaire.');
			}

			$emploiDuTemps = EmploiDuTemp::create([
				'debut' => $request->get('debut'),
				'fin' => $request->get('fin'),
				'uv_id' => $uvId,
				'type_programme' => $request->enum('type', TypeProgrammeEnum::class),
				'group_id' => $groupId,
				'salle_id' => $salleId,
				'owner_id' => $owner_id,
				'owner_type' => User::class,
				'details' => $request->get('details'),
				//				'evenement_id' => $evenementId,
				...injectAnneeScolaireId()
			]);

		} catch (Throwable $e) {
			return __500($e->getMessage());
		}

		return new EmploiDuTempsResource($emploiDuTemps);
	}

	public function destroy(Request $request): Response|ResponseFactory
	{
		$slug = $request->str('slug');
		$edt = EmploiDuTemp::query()->where('slug', $slug);
		if (!$edt)
			return __404('La suppression n\'a pas pu se faire du faire de l\'inexistence en base de donnée de l\'élément choisi');

		$edt->delete();
		return __200('Élément supprimé avec succès');
	}

	public function updateDates(Request $request): Response|ResponseFactory
	{
		$rules = [
			'debut' => ['required',],
			'fin' => ['required',],
		];

		$attributes = [
			'debut' => 'La date ou l\'heure de début',
			'fin' => 'La date ou l\'heure de fin',
		];

		$validator = validator($request->all(), $rules, [], $attributes);

		if ($validator->fails()) {
			return __422($validator->errors()->first());
		}

		try {
			$debut = Carbon::createFromFormat('Y-m-d\TH:i:s', $request->input('debut'));
			$fin = Carbon::createFromFormat('Y-m-d\TH:i:s', $request->input('fin'));
		} catch (Throwable) {
			return __500('Le format de la nouvelle date n\'est pas valable');
		}

		$edt = EmploiDuTemp::query()->firstWhere('slug', $request->input('slug'));

		if (!$edt) {
			return __404('La modification n\'a pas pu se faire : élément introuvable.');
		}

		if ($edt->fin->isBefore(now())) {
			return __422('Impossible de modifier : l\'événement est déjà terminé.');
		}

		if ($fin->lessThanOrEqualTo($debut)) {
			return __422("L'heure de fin doit être postérieure à l'heure de début.");
		}

		// Vérifier les chevauchements pour la même salle en excluant l'événement courant
		if ($this->hasSalleOverlap((int)$edt->getAttribute('salle_id'), $debut, $fin, (int)$edt->getAttribute('id'))) {
			return __422('Impossible de déplacer : la salle est déjà occupée sur cette plage horaire.');
		}

		try {
			$edt->update(compact('debut', 'fin'));
			Log::info('edt', [$edt]);
			Log::info('values', compact('debut', 'fin'));
		} catch (Throwable) {
			return __500();
		}

		return __200('Élément modifié avec succès');
	}

	/**
	 * Mise à jour d'une programmation (changement de salle par groupe, mise à jour des horaires, etc.)
	 */
	public function update(Request $request): Response|ResponseFactory|EmploiDuTempsResource
	{
		$rules = [
			'slug' => ['required', 'exists:emploi_du_temps,slug'],
			'salle' => ['nullable', 'exists:salles,slug'],
			'date' => ['nullable', 'date'],
			'debut' => ['nullable'],
			'fin' => ['nullable'],
			'uv_id' => ['nullable', 'exists:unite_valeurs,slug'],
			'type' => ['nullable', Rule::enum(TypeProgrammeEnum::class)],
			'grade' => ['nullable', 'exists:groups,slug'],
			'teacher' => ['nullable', 'exists:users,slug'],
			'details' => ['nullable'],
		];

		$validator = validator($request->all(), $rules);
		if ($validator->fails()) {
			return __422($validator->errors()->first());
		}

		$edt = EmploiDuTemp::query()->firstWhere('slug', $request->get('slug'));
		if (!$edt) return __404();

		$payload = [];
		// Compute datetime if provided
		if ($request->filled('date') && $request->filled('debut') && $request->filled('fin')) {
			try {
				$payload['debut'] = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('debut'));
				$payload['fin'] = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('fin'));
			} catch (Throwable) {
				return __500('Format de date/heure invalide');
			}
			if ($payload['fin']->lessThanOrEqualTo($payload['debut'])) {
				return __422("L'heure de fin doit être postérieure à l'heure de début.");
			}
		}

		if ($request->filled('salle')) {
			$payload['salle_id'] = Salle::query()->firstWhere('slug', $request->get('salle'))?->getAttribute('id');
		}
		if ($request->filled('grade')) {
			$payload['group_id'] = Group::query()->firstWhere('slug', $request->get('grade'))?->getAttribute('id');
		}
		if ($request->filled('uv_id')) {
			$payload['uv_id'] = UniteValeur::query()->firstWhere('slug', $request->get('uv_id'))?->getAttribute('id');
		}
		if ($request->filled('teacher')) {
			$payload['owner_id'] = User::query()->firstWhere('slug', $request->get('teacher'))?->getAttribute('id');
			$payload['owner_type'] = User::class;
		}
		if ($request->filled('type')) {
			$payload['type_programme'] = $request->enum('type', TypeProgrammeEnum::class);
		}
		if ($request->filled('details')) {
			$payload['details'] = $request->get('details');
		}

		// If salle/time provided, check overlap on target salle
		$salleIdToCheck = $payload['salle_id'] ?? (int)$edt->getAttribute('salle_id');
		$debutToCheck = $payload['debut'] ?? $edt->getAttribute('debut');
		$finToCheck = $payload['fin'] ?? $edt->getAttribute('fin');

		if ($this->hasSalleOverlap($salleIdToCheck, $debutToCheck, $finToCheck, (int)$edt->getAttribute('id'))) {
			return __422('Impossible de modifier : la salle est déjà occupée sur cette plage horaire.');
		}

		try {
			$edt->update($payload);
		} catch (Throwable $e) {
			return __500($e->getMessage());
		}

		return new EmploiDuTempsResource($edt->fresh());
	}

	/**
	 * Consultation de disponibilité d'une salle sur une plage [date + début, fin]
	 */
	public function checkAvailability(Request $request): Response|ResponseFactory
	{
		$rules = [
			'salle' => ['required', 'exists:salles,slug'],
			'date' => ['required', 'date'],
			'debut' => ['required'],
			'fin' => ['required'],
		];
		$validator = validator($request->all(), $rules);
		if ($validator->fails()) return __422($validator->errors()->first());

		try {
			$debut = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('debut'));
			$fin = Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('fin'));
		} catch (Throwable) {
			return __500('Format de date/heure invalide');
		}

		if ($fin->lessThanOrEqualTo($debut)) return __422("L'heure de fin doit être postérieure à l'heure de début.");

		$salleId = Salle::query()->firstWhere('slug', $request->get('salle'))?->getAttribute('id');
		$occupied = $this->hasSalleOverlap((int)$salleId, $debut, $fin);

		return response([
			'available' => !$occupied,
			'message' => $occupied ? 'Salle occupée sur cette plage horaire.' : 'Salle disponible.'
		]);
	}

	public function setEmploiDuTempsForUser(): void
	{

	}
}
