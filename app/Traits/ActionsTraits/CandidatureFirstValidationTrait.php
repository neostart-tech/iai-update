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

	/**
	 * Une fois le dossier transmis à l'académie, les décisions finales (valider,
	 * rejeter, réorienter, rectifier) ne sont plus de la responsabilité du chargé
	 * de la clientèle : seule l'académie (ou un compte à accès total) peut agir.
	 */
	private function utilisateurPeutAgirCommeAcademie(): bool
	{
		$user = auth('sanctum')->user() ?? auth()->user();

		return (bool) $user?->roles()->whereIn('slug', [
			'directeur-academique',
			'logiticien-academique',
			'admin',
			'directeur-general',
			'directeur-general-adjoint',
			'informaticien',
		])->exists();
	}

	private function refuserSiPasAcademie()
	{
		if ($this->utilisateurPeutAgirCommeAcademie()) {
			return null;
		}

		$message = "Cette action est réservée à l'académie une fois le dossier transmis.";

		if (request()->wantsJson() || request()->ajax()) {
			return response()->json(['success' => false, 'message' => $message], 403);
		}

		return redirect()->back()->with('error', $message);
	}

	/**
	 * Symétrique du précédent : transmettre le dossier à l'académie et demander une
	 * rectification sont l'apanage exclusif du chargé de la clientèle (ou d'un compte
	 * à accès total). L'académie ne doit jamais pouvoir transmettre elle-même un
	 * dossier, ni demander de rectification — chacun reste dans son rôle.
	 */
	private function utilisateurPeutAgirCommeChargeClientele(): bool
	{
		$user = auth('sanctum')->user() ?? auth()->user();

		return (bool) $user?->roles()->whereIn('slug', [
			'charge-de-la-clientele',
			'admin',
			'directeur-general',
			'directeur-general-adjoint',
			'informaticien',
		])->exists();
	}

	private function refuserSiPasChargeClientele()
	{
		if ($this->utilisateurPeutAgirCommeChargeClientele()) {
			return null;
		}

		$message = "Cette action est réservée au chargé de la clientèle.";

		if (request()->wantsJson() || request()->ajax()) {
			return response()->json(['success' => false, 'message' => $message], 403);
		}

		return redirect()->back()->with('error', $message);
	}

	/**
	 * Chargé de la clientèle : dossier vérifié et jugé complet, transmis à l'académie
	 * pour la décision finale (Valider/Rejeter/Rectifier/Réorienter — les 4 actions déjà
	 * existantes, qui deviennent l'apanage de l'académie une fois cette transmission faite).
	 */
	public function transmettreAcademie(Candidature $candidature)
	{
		if ($candidature->transmis_academie) {
			$message = "Ce dossier a déjà été transmis à l'académie.";

			if (request()->wantsJson() || request()->ajax()) {
				return response()->json(['success' => false, 'message' => $message], 422);
			}

			return redirect()->back()->with('error', $message);
		}

		if ($refus = $this->refuserSiPasChargeClientele()) {
			return $refus;
		}

		$candidature->update([
			'transmis_academie' => true,
			'transmis_academie_date' => now(),
		]);

		$message = $candidature->greeting(true) . ". Votre dossier a été vérifié et transmis à l'académie pour étude.";
		$candidature->notify(new \App\Notifications\Candidatures\CandidatTransmisAcademieNotification($message));

		$academiciens = \App\Models\User::whereHas('roles', function ($q) {
			$q->where('slug', 'directeur-academique');
		})->get();

		if ($academiciens->count() > 0) {
			\Illuminate\Support\Facades\Notification::send(
				$academiciens,
				new \App\Notifications\Candidatures\CandidatureTransmiseAcademieNotification($candidature)
			);
		}

		if (request()->wantsJson() || request()->ajax()) {
			return response()->json([
				'success' => true,
				'message' => "Dossier transmis à l'académie avec succès."
			]);
		}

		return redirect()->back()->with('success', "Dossier transmis à l'académie avec succès");
	}

	/**
	 * Décision finale de l'académie (acceptation) : n'est possible qu'une fois le dossier
	 * transmis par le chargé de la clientèle (transmettreAcademie ci-dessus).
	 */
	public function validateCandidature(Candidature $candidature)
	{
		if (!$candidature->transmis_academie) {
			$message = "Ce dossier doit d'abord être transmis à l'académie avant de pouvoir être validé.";

			if (request()->wantsJson() || request()->ajax()) {
				return response()->json(['success' => false, 'message' => $message], 422);
			}

			return redirect()->back()->with('error', $message);
		}

		if ($refus = $this->refuserSiPasAcademie()) {
			return $refus;
		}

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
		if (!$candidature->transmis_academie) {
			$message = "Ce dossier doit d'abord être transmis à l'académie avant de pouvoir être rejeté.";

			if ($request->wantsJson() || $request->ajax()) {
				return response()->json(['success' => false, 'message' => $message], 422);
			}

			return redirect()->back()->with('error', $message);
		}

		if ($refus = $this->refuserSiPasAcademie()) {
			return $refus;
		}

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
		// La demande de rectification est exclusivement une action du chargé de la
		// clientèle, et uniquement tant que le dossier ne lui a pas encore échappé
		// (avant transmission à l'académie). Une fois transmis, l'académie ne peut
		// que valider, rejeter ou réorienter — jamais demander de rectification.
		if ($candidature->transmis_academie) {
			$message = "Ce dossier a déjà été transmis à l'académie : seule celle-ci peut désormais valider, rejeter ou réorienter le dossier.";

			if ($request->wantsJson() || $request->ajax()) {
				return response()->json(['success' => false, 'message' => $message], 422);
			}

			return redirect()->back()->with('error', $message);
		}

		if ($refus = $this->refuserSiPasChargeClientele()) {
			return $refus;
		}

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
