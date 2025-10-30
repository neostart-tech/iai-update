<?php

namespace App\Http\Controllers;

use App\Http\Resources\Admin\EmploiDuTempsResource;
use App\Models\{Etudiant, User};
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MyCalendarController extends Controller
{
	public function __invoke(): ResponseFactory|Response|AnonymousResourceCollection
	{
		if ($user = Auth::user()) {
			/**
			 * @var User $user
			 */
			return EmploiDuTempsResource::collection($user->emploiDuTemps);
		}

		if ($user = Auth::guard('etudiants')->user()) {
			/**
			 * @var Etudiant $user
			 */
			return EmploiDuTempsResource::collection($user->group->emploiDuTemps);
		}

		return __500();
	}
}
