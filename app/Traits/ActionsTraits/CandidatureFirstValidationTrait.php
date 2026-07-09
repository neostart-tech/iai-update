<?php

namespace App\Traits\ActionsTraits;

use App\Jobs\SmsSendingProcess;
use App\Models\Candidature;
use App\Notifications\Candidatures\CandidatValideNotification;
use App\Services\ConcoursMatriculeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait CandidatureFirstValidationTrait
{
	/**
	 * Une candidature suit le mode concours (épreuve écrite) uniquement si elle est
	 * explicitement liée à une session qui le précise. Une candidature sans session
	 * (concours_session_id null) est une candidature antérieure à l'existence du
	 * système de concours : elle suit toujours le mode dossier uniquement, quel que
	 * soit le paramètre global actuel.
	 */
	private function candidatureSuitLeModeConcours(Candidature $candidature): bool
	{
		return (bool) $candidature->concoursSession?->avec_epreuve_ecrite;
	}

	public function validateCandidature(Candidature $candidature)
	{
		if ($this->candidatureSuitLeModeConcours($candidature)) {
			// Mode concours : validation du dossier uniquement, les étapes de paiement,
			// présence, notes et admission restent à traiter séparément.
			$candidature->update([
				'dossier_valide' => true,
				'validation_date' => now()
			]);

			if (!$candidature->matricule_concours) {
				$matricule = app(ConcoursMatriculeService::class)->generateFor($candidature);
				$candidature->update(['matricule_concours' => $matricule]);
			}

			$message = $candidature->greeting(true);
			// Pas encore d'espace candidat fonctionnel : phrase de connexion désactivée pour le moment (à réactiver plus tard).
			$message .= '. Votre dossier de candidature a été validé avec succès. Votre numéro de candidat est le ' . $candidature->matricule_concours . '. <!-- Connectez-vous régulièrement à votre compte pour suivre les prochaines étapes de votre procédure. -->';
		} else {
			// Mode dossier uniquement : la validation du dossier vaut admission directe
			// (pas de paiement de concours, présence, notes, ni décision d'admission séparée).
			$candidature->update([
				'dossier_valide' => true,
				'validation_date' => now(),
				'frais_paye' => true,
				'frai_paye_date' => now(),
				'participation' => true,
				'participation_date' => now(),
				'admission' => true,
				'admission_date' => now(),
			]);

			$message = $candidature->greeting(true);
			// Pas encore d'espace candidat fonctionnel : phrase de connexion désactivée pour le moment (à réactiver plus tard).
			$message .= '. Nous avons le plaisir de vous informer que, suite à l\'étude de votre dossier, votre candidature a été validée et admise. <!-- Connectez-vous régulièrement à votre compte pour suivre les prochaines étapes de votre inscription. -->';
		}

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

		$message = $candidature->greeting(true) . ". Votre dossier de candidature a été rejeté pour le motif suivant : " . $request->get('motif');
		$candidature->notify(new \App\Notifications\Candidatures\CandidatRejeteNotification($message, $request->get('motif')));

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

		$message = $candidature->greeting(true) . ". L'administration a examiné votre dossier et a demandé une rectification pour le motif suivant : " . $request->get('motif');
		$candidature->notify(new \App\Notifications\Candidatures\CandidatRectificationNotification($message, $request->get('motif')));

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
