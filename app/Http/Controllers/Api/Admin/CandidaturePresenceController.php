<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\CandidaturePresenceService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\{Request};
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated Utiliser CandidatureController::presenceControlStore (route candidature/presence-sub),
 * conservé et délégué au même service pour ne pas casser d'éventuels appelants existants.
 */
class CandidaturePresenceController extends Controller
{
	public function __invoke(Request $request, CandidaturePresenceService $service): Response|ResponseFactory
	{
		$absentSlugs = collect($request->get('candidats_absents', []));

		$result = $service->processPresence($absentSlugs);

		return response([
			'presentCandidats' => $result['presents']
		], Response::HTTP_OK);
	}
}
