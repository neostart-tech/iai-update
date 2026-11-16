<?php

namespace App\Traits\ActionsTraits;

use App\Models\Candidature;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Helpers\ConfigHelper as AppGetters;
use App\Http\Resources\CandidatureResource;
use App\Models\Filiere;
use App\Models\Niveau;

trait IndexTrait
{
	public function index()
	{
		$candidatures = Candidature::query()
			->with(['niveau', 'filiere', 'album'])
			->whereNotNull('soumis_le')
			->orderBy('nom')
			->orderBy('prenom')
			->get();
		return CandidatureResource::collection($candidatures);
	}

	/**
	 * Nombre de candidatures en attente d'action pour l'utilisateur connecté :
	 * - Chargé de la clientèle : dossiers pas encore transmis à l'académie.
	 * - Directeur académique / Logisticien académique : dossiers transmis,
	 *   en attente de la décision finale.
	 */
	public function countCandidaturesATraiter(Request $request)
	{
		$user = $request->user();
		if (!$user) {
			return response()->json(['count' => 0]);
		}

		$query = Candidature::query()
			->whereNotNull('soumis_le')
			->whereNull('motif')
			->where('rectification_expected', false)
			->where('dossier_valide', false);

		if ($user && method_exists($user, 'hasPermissionSlug')) {
			if ($user->hasPermissionSlug('valider-candidature') || $user->hasPermissionSlug('rejeter-candidature') || $user->hasPermissionSlug('reorienter-candidature')) {
				$query->where('transmis_academie', true);
			} elseif ($user->hasPermissionSlug('transmettre-candidature')) {
				$query->where('transmis_academie', false);
			} else {
				return response()->json(['count' => 0]);
			}
		} else {
			return response()->json(['count' => 0]);
		}

		return response()->json(['count' => $query->count()]);
	}

	public function exportEtudeDossierExcel(Request $request)
	{
		$query = Candidature::query()
			->with(['niveau', 'filiere', 'album', 'tuteurs', 'submittedDocuments'])
			->whereNotNull('soumis_le');

		if ($request->filled('ids')) {
			$query->whereIn('id', (array) $request->input('ids'));
		}

		$candidatures = $query
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$fileName = 'candidatures_etude_dossier_' . now()->format('Y-m-d') . '.xlsx';

		return \Maatwebsite\Excel\Facades\Excel::download(
			new \App\Exports\CandidaturesEtudeDossierExport($candidatures),
			$fileName
		);
	}


	public function inscriptionIndexForm()
	{
		return response()->json([
			'filieres' => Filiere::all(),
			'niveaux' => Niveau::all(),
			'metaData' => [
				'title' => 'Formulaire d\'inscription',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Formulaire d\'inscription'],
				'page_name' => 'Formulaire d\'inscription'
			]
		]);
	}



	public function payementCandidaturesIndex()
	{
		$payementCandidatures = Candidature::query()->with(['niveau', 'filiere', 'album'])
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->whereNull('motif')
			->where('frais_paye', false)
			->where('participation', false)
			->where('admission', false)
			->get();

		return response()->json([
			'data' => CandidatureResource::collection($payementCandidatures),
			'metaData' => [
				'title' => 'Payement des frais de participation',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Payement des frais de participation'],
				'page_name' => 'Payement des frais de participation'
			]
		]);
	}

	public function participantCandidaturesIndex()
	{
		$participantCandidatures = Candidature::query()
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->where('frais_paye', true)
			->where('participation', false)
			->where('admission', false)
			->whereNull('motif')
			->get();

		return response()->json([
			'data' => CandidatureResource::collection($participantCandidatures),
			'metaData' => [
				'title' => 'Contrôle de présence au concour',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Contrôle de présence au concour'],
				'page_name' => 'Contrôle de présence au concour'
			]
		]);
	}

	public function admisCandidaturesIndex()
	{
		$admisCandidatures = Candidature::query()
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->whereNotNull('validation_date')
			->where('frais_paye', true)
			->whereNotNull('frai_paye_date')
			->where('participation', true)
			->whereNotNull('participation_date')
			->where('admission', false)
			->whereNull('admission_date')
			->whereNull('motif')
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$title = 'Admission à ' . AppGetters::getAppName();

		return response()->json([
			'data' => CandidatureResource::collection($admisCandidatures),
			'metaData' => [
				'title' => $title,
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			]
		]);
	}


	public function InscriptionCandidaturesIndex()
	{
		$candidatures = Candidature::query()
			->with(['niveau', 'filiere', 'album', 'etudiant'])
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->where('admission', true)
			->whereNull('motif')
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$title = 'Inscription à ' . AppGetters::getAppName();

		return response()->json([
			'data' => CandidatureResource::collection($candidatures),
			'metaData' => [
				'title' => $title,
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			]
		]);
	}


	public function exportCandidatsAdmisExcel()
	{
		$candidatures = Candidature::query()
			->with(['niveau', 'filiere'])
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->whereNotNull('validation_date')
			->where('frais_paye', true)
			->whereNotNull('frai_paye_date')
			->where('participation', true)
			->whereNotNull('participation_date')
			->where('admission', true)
			->whereNotNull('admission_date')
			->whereNull('motif')
			->where(function ($query) {
				$query->whereDoesntHave('etudiant')
					->orWhereHas('etudiant', function ($q) {
						$q->whereDoesntHave('groups');
					});
			})
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$fileName = 'candidats_admis_' . now()->format('Y-m-d') . '.xlsx';

		return \Maatwebsite\Excel\Facades\Excel::download(
			new \App\Exports\CandidatsAdmisExport($candidatures),
			$fileName
		);
	}

	public function liste_des_admis()
	{
		$admisCandidatures = Candidature::query()
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->where('admission', true)
			->whereNull('motif')
			->orderBy('nom')
			->orderBy('prenom')
			->get();

		$title = 'Admis à ' . AppGetters::getAppName();

		return response()->json([
			'data' => CandidatureResource::collection($admisCandidatures),
			'metaData' => [
				'title' => $title,
				'breadcrumbs' => ['Administration', 'Candidatures', $title],
				'page_name' => $title
			]
		]);
	}

	public function liste_des_rectifications()
	{
		return response()->json([
			'data' => CandidatureResource::collection(Candidature::query()->whereNotNull('soumis_le')->where('rectification_expected', true)->get()),
			'metaData' => [
				'title' => 'Liste des candidatures en attente de rectification',
				'page_name' => 'Liste des candidatures en attente de rectification'
			]
		]);
	}

	public function liste_des_rejets()
	{
		return response()->json([
			'data' => CandidatureResource::collection(Candidature::query()->whereNotNull('soumis_le')->where('dossier_valide', false)->whereNotNull('motif')->get())
		]);
	}

	/**
	 * Dossiers commencés via l'inscription en plusieurs étapes (escen-website) mais
	 * jamais terminés (soumis_le encore vide) — permet au staff de relancer ces
	 * candidats par téléphone/email plutôt que de les perdre silencieusement.
	 */
	public function listeDossiersIncomplets()
	{
		$dossiers = Candidature::query()
			->whereNull('soumis_le')
			->orderByDesc('created_at')
			->get(['id', 'slug', 'nom', 'prenom', 'email', 'tel', 'type_diplome_id', 'niveau_id', 'created_at', 'updated_at']);

		$data = $dossiers->map(function (Candidature $c) {
			// Étape la plus avancée atteinte, déduite des champs déjà renseignés
			// (le dossier n'a pas encore de statut d'étape dédié en base).
			$etape = 'Identité & Coordonnées';
			if ($c->type_diplome_id) $etape = 'Diplôme';
			if ($c->niveau_id) $etape = 'Documents';

			return [
				'id' => $c->id,
				'slug' => $c->slug,
				'nom' => $c->nom,
				'prenom' => $c->prenom,
				'email' => $c->email,
				'tel' => $c->tel,
				'derniere_etape_atteinte' => $etape,
				'commence_le' => $c->created_at,
				'derniere_activite_le' => $c->updated_at,
			];
		});

		return response()->json([
			'data' => $data,
			'metaData' => [
				'title' => 'Dossiers incomplets',
				'breadcrumbs' => ['Administration', 'Candidatures', 'Dossiers incomplets'],
				'page_name' => 'Dossiers incomplets',
			],
		]);
	}

	public function supprimerBrouillon(Candidature $candidature)
	{
		if ($candidature->soumis_le) {
			return response()->json([
				'success' => false,
				'message' => "Ce dossier est déjà soumis, il ne peut pas être supprimé comme un brouillon.",
			], 422);
		}

		$candidature->delete();

		return response()->json(['success' => true]);
	}

	/**
	 * Suppression logique (Soft Delete) d'une candidature avec journalisation complète.
	 */
	public function destroy(Request $request, Candidature $candidature)
	{
		$user = $request->user();
		$nomCandidat = trim("{$candidature->nom} {$candidature->prenom}");
		$numDossier = $candidature->numero_dossier_affiche ?? $candidature->code ?? ('#' . $candidature->id);

		// Exécution du soft delete
		$candidature->delete();

		// Journal d'activité pour traçabilité (qui, qui a été supprimé, et quand)
		if (function_exists('activity')) {
			activity('candidatures')
				->performedOn($candidature)
				->causedBy($user)
				->withProperties([
					'action' => 'suppression',
					'candidature_id' => $candidature->id,
					'nom_complet' => $nomCandidat,
					'email' => $candidature->email,
					'numero_dossier' => $numDossier,
					'filiere' => $candidature->filiere?->nom,
					'niveau' => $candidature->niveau?->libelle ?? $candidature->niveau?->nom,
					'supprime_par' => $user ? trim("{$user->nom} {$user->prenom}") : 'Système',
					'supprime_le' => now()->toDateTimeString(),
				])
				->log("Suppression (mise en corbeille) de la candidature de {$nomCandidat} ({$numDossier})");
		}

		return response()->json([
			'success' => true,
			'message' => "La candidature de {$nomCandidat} a été mise en corbeille avec succès.",
		]);
	}

	/**
	 * Suppression groupée (Soft Delete) de plusieurs candidatures avec journal d'activité.
	 */
	public function bulkDestroy(Request $request)
	{
		$request->validate([
			'ids' => 'required|array|min:1',
		]);

		$user = $request->user();
		$identifiers = $request->input('ids');

		$candidatures = Candidature::where(function ($q) use ($identifiers) {
			$q->whereIn('id', $identifiers)
				->orWhereIn('slug', $identifiers);
		})->get();

		if ($candidatures->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => 'Aucune candidature trouvée pour les identifiants fournis.',
			], 404);
		}

		$deletedCount = 0;
		$deletedDetails = [];

		foreach ($candidatures as $candidature) {
			$nomCandidat = trim("{$candidature->nom} {$candidature->prenom}");
			$numDossier = $candidature->numero_dossier_affiche ?? $candidature->code ?? ('#' . $candidature->id);

			$candidature->delete();
			$deletedCount++;

			$deletedDetails[] = [
				'id' => $candidature->id,
				'slug' => $candidature->slug,
				'nom_complet' => $nomCandidat,
				'numero_dossier' => $numDossier,
			];

			if (function_exists('activity')) {
				activity('candidatures')
					->performedOn($candidature)
					->causedBy($user)
					->withProperties([
						'action' => 'suppression_groupee',
						'candidature_id' => $candidature->id,
						'nom_complet' => $nomCandidat,
						'email' => $candidature->email,
						'numero_dossier' => $numDossier,
						'filiere' => $candidature->filiere?->nom,
						'niveau' => $candidature->niveau?->libelle ?? $candidature->niveau?->nom,
						'supprime_par' => $user ? trim("{$user->nom} {$user->prenom}") : 'Système',
						'supprime_le' => now()->toDateTimeString(),
					])
					->log("Suppression groupée : mise en corbeille de la candidature de {$nomCandidat} ({$numDossier})");
			}
		}

		if (function_exists('activity')) {
			activity('candidatures')
				->causedBy($user)
				->withProperties([
					'action' => 'suppression_groupee_summary',
					'total_supprimes' => $deletedCount,
					'candidats' => $deletedDetails,
					'supprime_par' => $user ? trim("{$user->nom} {$user->prenom}") : 'Système',
					'date' => now()->toDateTimeString(),
				])
				->log("Suppression groupée de {$deletedCount} candidature(s) par " . ($user ? trim("{$user->nom} {$user->prenom}") : 'Système'));
		}

		return response()->json([
			'success' => true,
			'message' => "{$deletedCount} candidature(s) ont été mises en corbeille avec succès.",
			'count' => $deletedCount,
		]);
	}

	/**
	 * Restauration groupée de plusieurs candidatures avec journal d'activité.
	 */
	public function bulkRestore(Request $request)
	{
		$request->validate([
			'ids' => 'required|array|min:1',
		]);

		$user = $request->user();
		$identifiers = $request->input('ids');

		$candidatures = Candidature::onlyTrashed()
			->where(function ($q) use ($identifiers) {
				$q->whereIn('id', $identifiers)
					->orWhereIn('slug', $identifiers);
			})->get();

		if ($candidatures->isEmpty()) {
			return response()->json([
				'success' => false,
				'message' => 'Aucune candidature trouvée dans la corbeille pour les identifiants fournis.',
			], 404);
		}

		$restoredCount = 0;
		$restoredDetails = [];

		foreach ($candidatures as $candidature) {
			$nomCandidat = trim("{$candidature->nom} {$candidature->prenom}");
			$numDossier = $candidature->numero_dossier_affiche ?? $candidature->code ?? ('#' . $candidature->id);

			$candidature->restore();
			$restoredCount++;

			$restoredDetails[] = [
				'id' => $candidature->id,
				'slug' => $candidature->slug,
				'nom_complet' => $nomCandidat,
				'numero_dossier' => $numDossier,
			];

			if (function_exists('activity')) {
				activity('candidatures')
					->performedOn($candidature)
					->causedBy($user)
					->withProperties([
						'action' => 'restauration_groupee',
						'candidature_id' => $candidature->id,
						'nom_complet' => $nomCandidat,
						'email' => $candidature->email,
						'numero_dossier' => $numDossier,
						'restaure_par' => $user ? trim("{$user->nom} {$user->prenom}") : 'Système',
						'restaure_le' => now()->toDateTimeString(),
					])
					->log("Restauration groupée : rétablissement de la candidature de {$nomCandidat} ({$numDossier})");
			}
		}

		if (function_exists('activity')) {
			activity('candidatures')
				->causedBy($user)
				->withProperties([
					'action' => 'restauration_groupee_summary',
					'total_restaures' => $restoredCount,
					'candidats' => $restoredDetails,
					'restaure_par' => $user ? trim("{$user->nom} {$user->prenom}") : 'Système',
					'date' => now()->toDateTimeString(),
				])
				->log("Restauration groupée de {$restoredCount} candidature(s) par " . ($user ? trim("{$user->nom} {$user->prenom}") : 'Système'));
		}

		return response()->json([
			'success' => true,
			'message' => "{$restoredCount} candidature(s) ont été restaurées avec succès.",
			'count' => $restoredCount,
		]);
	}

	/**
	 * Liste des candidatures mises en corbeille (soft-deleted).
	 */
	public function corbeille(Request $request)
	{
		$candidatures = Candidature::onlyTrashed()
			->with(['niveau', 'filiere', 'album'])
			->orderByDesc('deleted_at')
			->get();

		return CandidatureResource::collection($candidatures);
	}

	/**
	 * Restauration d'une candidature supprimée.
	 */
	public function restore(Request $request, $id)
	{
		$user = $request->user();

		$candidature = Candidature::onlyTrashed()
			->where(function ($q) use ($id) {
				$q->where('id', $id)->orWhere('slug', $id);
			})
			->firstOrFail();

		$nomCandidat = trim("{$candidature->nom} {$candidature->prenom}");
		$numDossier = $candidature->numero_dossier_affiche ?? $candidature->code ?? ('#' . $candidature->id);

		$candidature->restore();

		if (function_exists('activity')) {
			activity('candidatures')
				->performedOn($candidature)
				->causedBy($user)
				->withProperties([
					'action' => 'restauration',
					'candidature_id' => $candidature->id,
					'nom_complet' => $nomCandidat,
					'email' => $candidature->email,
					'numero_dossier' => $numDossier,
					'restaure_par' => $user ? trim("{$user->nom} {$user->prenom}") : 'Système',
					'restaure_le' => now()->toDateTimeString(),
				])
				->log("Restauration de la candidature de {$nomCandidat} ({$numDossier})");
		}

		return response()->json([
			'success' => true,
			'message' => "La candidature de {$nomCandidat} a été restaurée avec succès.",
			'candidature' => new CandidatureResource($candidature),
		]);
	}

	/**
	 * Suppression définitive d'une candidature de la base de données.
	 */
	public function forceDelete(Request $request, $id)
	{
		$user = $request->user();

		$candidature = Candidature::onlyTrashed()
			->where(function ($q) use ($id) {
				$q->where('id', $id)->orWhere('slug', $id);
			})
			->firstOrFail();

		$nomCandidat = trim("{$candidature->nom} {$candidature->prenom}");
		$numDossier = $candidature->numero_dossier_affiche ?? $candidature->code ?? ('#' . $candidature->id);

		$candidature->forceDelete();

		if (function_exists('activity')) {
			activity('candidatures')
				->causedBy($user)
				->withProperties([
					'action' => 'suppression_definitive',
					'candidature_id' => $id,
					'nom_complet' => $nomCandidat,
					'numero_dossier' => $numDossier,
					'supprime_definitivement_par' => $user ? trim("{$user->nom} {$user->prenom}") : 'Système',
					'date' => now()->toDateTimeString(),
				])
				->log("Suppression définitive de la candidature de {$nomCandidat} ({$numDossier})");
		}

		return response()->json([
			'success' => true,
			'message' => "La candidature de {$nomCandidat} a été définitivement supprimée.",
		]);
	}

	public function chooseClassAssignmentGroupView()
	{
		return response()->json([
			'groups' => Group::query()
				->with(['filieres:id,code,nom', 'niveau:id,libelle'])
				->withCount('etudiants')
				->orderBy('nom')
				->get(['id', 'nom', 'slug', 'niveau_id']),
		]);
	}

	public function showGroupClassAssignmentView(Group $group)
	{
		$candidatures = Candidature::query()
			->whereNotNull('soumis_le')
			->where('dossier_valide', true)
			->where('admission', true)
			->whereNull('motif')
			->whereNull('acceptation_date')
			->whereNull('etudiant_id')
			->whereNull('group_id') // <--- IL DISPARAIT DE LA LISTE ICI !
			->where('niveau_id', $group->niveau_id)
			->whereIn('filiere_id', $group->filieres()->pluck('filieres.id'))
			->get();

		return response()->json([
			'group' => $group->load('filieres:id,code,nom'),
			'candidatures' => CandidatureResource::collection($candidatures)
		]);
	}
}
