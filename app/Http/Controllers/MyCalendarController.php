<?php

namespace App\Http\Controllers;

use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Models\{EmploiDuTemp, Etudiant, User};
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MyCalendarController extends Controller
{
	public function __invoke(): ResponseFactory|Response|AnonymousResourceCollection
	{
		$user = Auth::user() ?? Auth::guard('etudiants')->user();

		if ($user instanceof Etudiant) {
			/** @var Etudiant $user */
			$group = $user->group()->first() ?? $user->allEtudiantGroups()->first()?->group;
			if (!$group) {
				return EmploiDuTempsResource::collection(collect());
			}

			$cours = $group->emploiDuTemps()
				->with(['uv', 'salle', 'owner'])
				->get()
				->filter(fn(EmploiDuTemp $c) => $user->estConcerneParCours($c))
				->values();

			return EmploiDuTempsResource::collection($cours);
		}

		if ($user instanceof User) {
			/** @var User $user */
			return EmploiDuTempsResource::collection($user->emploiDuTemps);
		}

		return __500();
	}
}
