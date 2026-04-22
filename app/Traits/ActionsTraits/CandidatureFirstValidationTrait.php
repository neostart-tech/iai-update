<?php

namespace App\Traits\ActionsTraits;

use App\Jobs\SmsSendingProcess;
use App\Models\Candidature;
use App\Notifications\Candidatures\CandidatValideNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait CandidatureFirstValidationTrait
{
	// public function validateCandidature(Candidature $candidature): RedirectResponse
	// {
	// 	$candidature->update(['dossier_valide' => true, 'validation_date' => now()]);

	// 	// SmsSendingProcess::dispatch(
	// 	// 	$candidature->getAttribute('tel'),
	// 	// 	'Votre dossier de candidature a été validé avec succès. Connectez-vous à votre compte pour voir l\'étape suivante de votre procédure d\'admission.'
	// 	// );

	// 	$message = $candidature->greeting(true);
	// 	$message .= '. Votre dossier de candidature a été validé avec succès. Connectez-vous  régulièrement à votre compte pour suivre les prochaines étapes de votre procédure.';

	// 	$candidature->notify(new CandidatValideNotification($message));

	// 	return to_route('admin.candidatures.index')->with(successMsg('Candidature validée avec succès'));
	// }

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

		// SmsSendingProcess::dispatch(
		// 	$candidature->getAttribute('tel'),
		// 	'Votre dossier de candidature a été validé avec succès. Connectez-vous à votre compte pour voir l\'étape suivante de votre procédure d\'admission.'
		// );

		$message = $candidature->greeting(true);
		$message .= '. Nous avons le plaisir de vous informer que, suite à l\'étude approfondie de votre dossier par la commission d\'admission, votre candidature a été approuvée. Connectez-vous régulièrement à votre compte pour suivre les prochaines étapes de votre procédure.';

		$candidature->notify(new CandidatValideNotification($message));
		
		return response()->json([
			'success' => true,
			'message' => 'Candidature certifiée avec succès.'
		]);
	}

	public function rejeter(Request $request, Candidature $candidature)
	{
		$request->validate([
			'motif' => ['required'],
			[
				'motif' => [
					'required' => 'Le motif de rejet de la candidature est obligatoire'
				]
			]
		]);

		$candidature->update([
			'dossier_valide' => false,
			'validation_date' => now(),
			'motif' => $request->get('motif')
		]);

		// SmsSendingProcess::dispatch(
		// 	$candidature->getAttribute('tel'),
		// 	'Votre dossier de candidature a été rejeté. Connectez-vous à votre compte pour voir le motif.'
		// );

		// Todo Notifier le candidat concerné de la validation de sa candidature
		return response()->json([
			'success' => true,
			'message' => 'Candidature rejetée avec succès.'
		]);
	}

	public function rectifier(Request $request, Candidature $candidature)
	{
		$candidature->update([
			'motif' => $request->get('motif'),
			'rectification_expected' => true
		]);

		// SmsSendingProcess::dispatch(
		// 	$candidature->getAttribute('tel'),
		// 	'Votre dossier de candidature a été estimé incomplet et des rectifications d\'informations ont été exigées de votre part. 
		// 	 Connectez-vous à votre compte pour faire les dites rectifications afin de poursuivre la procédure.'
		// );

		// Todo Notifier le candidat concerné de la validation de sa candidature
		return response()->json([
			'success' => true,
			'message' => 'Demande de rectification envoyée avec succès.'
		]);
	}
}
