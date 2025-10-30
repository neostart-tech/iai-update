<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\{Request};
use Symfony\Component\HttpFoundation\Response;

class CandidaturePresenceController extends Controller
{
	public function __invoke(Request $request): Response|ResponseFactory
	{
		$candidatsSlugs = $request->get('candidats');

		$candidats = Candidature::query()->whereIn('slug', $candidatsSlugs)->get();
//		dd($candidats);

		$presentCandidats = [];

		foreach ($candidats as $candidat) {
			/**
			 * @var Candidature $candidat
			 */
			$candidat->update([
				'participation' => true,
				'participation_date' => now()
			]);

			$presentCandidats[] = $candidat->getAttribute('slug');
		}

		return response([
			'presentCandidats' => $presentCandidats
		], Response::HTTP_OK);
	}
}
