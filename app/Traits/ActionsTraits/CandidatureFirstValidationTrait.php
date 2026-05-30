<?php

namespace App\Traits\ActionsTraits;

use App\Jobs\SmsSendingProcess;
use App\Models\Candidature;
use App\Notifications\Candidatures\CandidatValideNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait CandidatureFirstValidationTrait
{
	public function validateCandidature(Candidature $candidature)
	{
		$candidature->update([
			'dossier_valide' => true,
			'validation_date' => now()
		]);

		$message = $candidature->greeting(true);
		$message .= '. Votre dossier de candidature a été validé avec succès. Connectez-vous régulièrement à votre compte pour suivre les prochaines étapes de votre procédure.';

		$candidature->notify(new CandidatValideNotification($message));

		if (request()->wantsJson() || request()->ajax()) {
			return response()->json([
				'success' => true,
				'message' => 'Candidature validée avec succès.'
			]);
		}

		return redirect()->back()->with('success', 'Candidature validée avec succès');
	}

	public function rejectCandidature(Request $request, Candidature $candidature)
	{
		$request->validate([
			'motif' => ['required']
		]);

		$candidature->update([
			'dossier_valide' => false,
			'validation_date' => now(),
			'motif' => $request->get('motif')
		]);

		if (request()->wantsJson() || request()->ajax()) {
			return response()->json([
				'success' => true,
				'message' => 'Candidature rejetée avec succès.'
			]);
		}

		return redirect()->back()->with('success', 'Candidature rejetée avec succès');
	}

	public function askForRectificationOnCandidature(Request $request, Candidature $candidature)
	{
		$request->validate([
			'motif' => ['required']
		]);

		$candidature->update([
			'motif' => $request->get('motif'),
			'rectification_expected' => true
		]);

		if (request()->wantsJson() || request()->ajax()) {
			return response()->json([
				'success' => true,
				'message' => 'Demande de rectification envoyée avec succès.'
			]);
		}

		return redirect()->back()->with('success', 'Demande de rectification envoyée avec succès');
	}

	/**
	 * @deprecated Use validateCandidature instead for standard validation.
	 * This method performs a full admission in one step.
	 */
	public function valider(Candidature $candidature)
	{
		$candidature->update([
			'dossier_valide' => true,
			'validation_date' => now(),
			"frais_paye" => true,
			"frai_paye_date" => now(),
			"participation" => true,
			"participation_date" => now(),
			"admission" => true,
			"admission_date" => now(),
		]);

		$message = $candidature->greeting(true);
		$message .= '. Nous avons le plaisir de vous informer que, suite à l\'étude approfondie de votre dossier par la commission d\'admission, votre candidature a été approuvée.';

		$candidature->notify(new CandidatValideNotification($message));
		
		return response()->json([
			'success' => true,
			'message' => 'Candidature certifiée avec succès.'
		]);
	}

	public function rejeter(Request $request, Candidature $candidature)
	{
		return $this->rejectCandidature($request, $candidature);
	}

	public function rectifier(Request $request, Candidature $candidature)
	{
		return $this->askForRectificationOnCandidature($request, $candidature);
	}
}
