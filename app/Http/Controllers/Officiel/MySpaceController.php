<?php

namespace App\Http\Controllers\Officiel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Candidatures\CandidatRectificationSubmittedNotification;
use Illuminate\View\View;

class MySpaceController extends Controller
{
	public function show()
	{
		$candidature = auth('sanctum')->user() ?? auth('web_candidatures')->user();

		if (request()->wantsJson() || request()->is('api/*')) {
			$requirements = [];

			if ($candidature) {
				$candidature->load(['tuteur', 'tuteurs', 'responsable', 'niveau', 'filiere', 'album', 'submittedDocuments']);

				// Fusionne les champs historiques de l'album (photo, naissance, ...) avec
				// les documents dynamiques soumis (submittedDocuments), même logique que
				// CandidatureController::show() côté admin.
				$originalAlbum = $candidature->album ? $candidature->album->toArray() : [];
				$albumFiles = [];
				foreach ($candidature->submittedDocuments as $doc) {
					$albumFiles[$doc->document_key] = $doc->file_path;
				}
				unset($candidature->album);
				$candidature->setAttribute('album', (object) array_merge($originalAlbum, $albumFiles));

				if ($candidature->niveau_id) {
					$requirements = \App\Models\DocumentRequirement::with('documentType')
						->where('niveau_id', $candidature->niveau_id)
						->where(function ($q) use ($candidature) {
							$q->whereNull('filiere_id')->orWhere('filiere_id', $candidature->filiere_id);
						})->get()->map(fn ($req) => [
							'niveau_id' => $req->niveau_id,
							'filiere_id' => $req->filiere_id,
							'is_obligatoire' => (bool) $req->is_obligatoire,
							'document_key' => $req->documentType?->document_key,
							'nom_affichage' => $req->documentType?->nom_affichage,
							'is_multiple' => (bool) ($req->documentType?->is_multiple ?? false),
							'accepted_formats' => $req->documentType?->accepted_formats ?? 'all',
							'description' => $req->description,
						])->filter(fn ($req) => $req['document_key'] !== null)->values();
				}
			}

			return response()->json([
				'candidature' => $candidature,
				'expected_docs' => $requirements,
			]);
		}

		return view('officiel.mySpace.show')->with([
			'candidature' => $candidature
		]);
	}

	public function myFiles()
	{
		$user = auth('sanctum')->user() ?? auth('web_candidatures')->user();

		$requirements = [];
		if ($user && $user->niveau_id) {
			$requirements = \App\Models\DocumentRequirement::with('documentType')
				->where('niveau_id', $user->niveau_id)
				->where(function($q) use ($user) {
					$q->whereNull('filiere_id')->orWhere('filiere_id', $user->filiere_id);
				})->get()->map(fn ($req) => [
					'niveau_id' => $req->niveau_id,
					'filiere_id' => $req->filiere_id,
					'is_obligatoire' => (bool) $req->is_obligatoire,
					'document_key' => $req->documentType?->document_key,
					'nom_affichage' => $req->documentType?->nom_affichage,
					'is_multiple' => (bool) ($req->documentType?->is_multiple ?? false),
					'accepted_formats' => $req->documentType?->accepted_formats ?? 'all',
					'description' => $req->description,
				])->filter(fn ($req) => $req['document_key'] !== null)->values();
		}

		$album = [];
		$metadata = [];
		if ($user) {
			if ($user->album) {
				$album = $user->album->toArray();
			}
			foreach ($user->submittedDocuments as $doc) {
				$album[$doc->document_key] = $doc->file_path;
			}
			foreach ($album as $key => $path) {
				if ($path && is_string($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
					$metadata[$key] = [
						'size' => \Illuminate\Support\Facades\Storage::disk('public')->size($path),
						'date' => null,
					];
				}
			}
			foreach ($user->submittedDocuments as $doc) {
				if (isset($metadata[$doc->document_key])) {
					$metadata[$doc->document_key]['date'] = $doc->created_at;
				}
			}
		}

		if (request()->wantsJson() || request()->is('api/*')) {
			\Illuminate\Support\Facades\Log::info('MyFiles API Response:', ['user_id' => $user ? $user->id : null, 'album' => $album, 'req_count' => count($requirements)]);
			return response()->json([
				'documents' => $album,
				'documents_metadata' => $metadata,
				'candidature' => $user,
				'expected_docs' => $requirements
			]);
		}

		return view('officiel.mySpace.files')->with([
			'album' => $user ? $user->album : null
		]);
	}

	public function constitution()
	{
		$candidature = auth('sanctum')->user() ?? auth('web_candidatures')->user();

		if (request()->wantsJson() || request()->is('api/*')) {
			return response()->json([
				'candidature' => $candidature
			]);
		}

		return view('officiel.mySpace.constitution')->with([
			'candidature' => $candidature
		]);
	}

	public function myPayements(): View
	{
		return view('layouts.coming-soon');
	}

	public function uploadFile(\Illuminate\Http\Request $request)
	{
		$user = auth('sanctum')->user() ?? auth('web_candidatures')->user();
		if (!$user) return response()->json(['message' => 'Non autorisé'], 401);

		$request->validate([
			'document' => 'required',
			'type' => 'required|string'
		]);

		$type = $request->input('type');

		// Format validation
		$docType = \App\Models\DocumentType::where('document_key', $type)->first();
		if ($docType) {
			if ($docType->accepted_formats === 'image') {
				$request->validate([
					'document' => 'image|mimes:jpeg,png,jpg,gif,webp',
					'document.*' => 'image|mimes:jpeg,png,jpg,gif,webp',
				], [
					'document.image' => "Le document {$docType->nom_affichage} doit être une image.",
					'document.mimes' => "Le document {$docType->nom_affichage} doit être au format jpeg, png, jpg, gif ou webp."
				]);
			} elseif ($docType->accepted_formats === 'pdf') {
				$request->validate([
					'document' => 'mimes:pdf',
					'document.*' => 'mimes:pdf',
				], [
					'document.mimes' => "Le document {$docType->nom_affichage} doit être un fichier PDF."
				]);
			}
		}
		$filePrefix = \Illuminate\Support\Str::slug($user->nom . '_' . $user->prenom);
		
		$folder = 'documents/' . $type;

		$documentInput = $request->file('document');
		
		if (is_array($documentInput)) {
			$paths = [];
			foreach ($documentInput as $file) {
				$paths[] = $file->store($folder . '/' . $filePrefix, 'public');
			}
			$path = json_encode($paths);
		} else {
			$path = $documentInput->store($folder . '/' . $filePrefix, 'public');
		}

		// Mise à jour de l'album dynamique
		$user->submittedDocuments()->updateOrCreate(
			['document_key' => $type],
			['file_path' => $path, 'statut' => 'en_attente']
		);

		// Optionnel : Changer le statut pour indiquer que le candidat a répondu à la rectification
		// $user->update(['rectification_expected' => false, 'statut' => 'Rectifié par le candidat']);

		return response()->json([
			'success' => true,
			'message' => 'Document mis à jour avec succès',
			'path' => $path
		]);
	}
	public function updateProfil(\Illuminate\Http\Request $request)
	{
		$candidature = auth('sanctum')->user() ?? auth('web_candidatures')->user();
		if (!$candidature) return response()->json(['message' => 'Non autorisé'], 401);

		$request->validate([
			'email' => [
				'nullable',
				'email',
				'max:255',
				'unique:candidatures,email,' . $candidature->id
			]
		]);

		$data = $request->only([
			'nom', 'prenom', 'nom_jeune_fille', 'numero_table', 'annee_bac', 'serie', 'mention_bac',
			'genre', 'date_naissance', 'lieu_naissance', 'email',
			'nationalite', 'hobbit', 'tel', 'tel2', 'tel3', 'bp', 'fax', 'niveau_id', 'filiere_id', 'adresse'
		]);

		if ($request->has('type_diplome')) {
			$data['dernier_diplome'] = $request->get('type_diplome');
		}
		if ($request->has('etablissement_diplome')) {
			$data['etablissement_diplome'] = $request->get('etablissement_diplome');
		}

		$candidature->update($data);

		// Tuteurs répétables + responsable des frais — même logique que
		// CandidatureController::store()/updateByAdmin() : on remplace la liste
		// existante par celle envoyée.
		if ($request->has('tuteurs')) {
			$candidature->tuteurs()->delete();
			$candidature->responsable()->delete();

			$tuteursValides = [];
			foreach ($request->input('tuteurs', []) as $tuteurEntry) {
				if (blank($tuteurEntry['nom'] ?? null) && blank($tuteurEntry['prenom'] ?? null)) {
					continue;
				}

				$donneesTuteur = [
					'nom' => $tuteurEntry['nom'] ?? null,
					'prenom' => $tuteurEntry['prenom'] ?? null,
					'profession' => $tuteurEntry['profession'] ?? null,
					'employeur' => $tuteurEntry['employeur'] ?? null,
					'email' => $tuteurEntry['email'] ?? null,
					'tel' => $tuteurEntry['tel'] ?? null,
					'adresse' => $tuteurEntry['adresse'] ?? null,
					'responsable_des_frais' => filter_var($tuteurEntry['responsable_des_frais'] ?? false, FILTER_VALIDATE_BOOLEAN),
				];

				$candidature->tuteurs()->create($donneesTuteur);
				$tuteursValides[] = $donneesTuteur;
			}

			if (!empty($tuteursValides)) {
				$responsableData = collect($tuteursValides)->firstWhere('responsable_des_frais', true) ?? $tuteursValides[0];
				$candidature->responsable()->create(collect($responsableData)->except('responsable_des_frais')->all());
			}
		}

		$candidature->load(['tuteur', 'tuteurs', 'responsable', 'niveau', 'filiere']);

		return response()->json([
			'success' => true,
			'message' => 'Profil mis à jour avec succès.',
			'candidature' => $candidature
		]);
	}

	public function submitRectification(\Illuminate\Http\Request $request)
	{
		$candidature = auth('sanctum')->user() ?? auth('web_candidatures')->user();
		if (!$candidature) return response()->json(['message' => 'Non autorisé'], 401);

		$candidature->update([
			'rectification_expected' => false,
			'motif' => null
		]);

		// Notifier le responsable actuel du dossier : le chargé de la clientèle tant
		// que le dossier n'a pas été transmis à l'académie, sinon l'académie elle-même
		// (même logique que le reste du circuit de validation).
		$rolesANotifier = $candidature->transmis_academie
			? ['directeur-academique', 'logiticien-academique']
			: ['charge-de-la-clientele'];

		$responsables = User::whereHas('roles', function ($q) use ($rolesANotifier) {
			$q->whereIn('slug', $rolesANotifier);
		})->get();

		if ($responsables->count() > 0) {
			Notification::send($responsables, new CandidatRectificationSubmittedNotification($candidature));
		}

		$candidature->load(['tuteur', 'tuteurs', 'responsable', 'niveau', 'filiere']);

		return response()->json([
			'success' => true,
			'message' => 'Dossier soumis à nouveau avec succès.',
			'candidature' => $candidature
		]);
	}

	public function notifications()
	{
		$candidature = auth('sanctum')->user() ?? auth('web_candidatures')->user();
		if (!$candidature) return response()->json(['message' => 'Non autorisé'], 401);

		$notifications = $candidature->notifications->map(function ($notification) {
			$data = $notification->data;
			return [
				'id' => $notification->id,
				'titre' => $data['title'] ?? 'Notification',
				'contenu' => $data['content'] ?? '',
				'type' => $data['level'] ?? 'info',
				'lu' => $notification->read_at !== null,
				'created_at' => $notification->created_at,
			];
		});

		return response()->json([
			'success' => true,
			'notifications' => $notifications
		]);
	}

	public function markNotificationAsRead($id)
	{
		$candidature = auth('sanctum')->user() ?? auth('web_candidatures')->user();
		if (!$candidature) return response()->json(['message' => 'Non autorisé'], 401);

		$notification = $candidature->notifications()->where('id', $id)->first();
		if ($notification) {
			$notification->markAsRead();
		}

		return response()->json(['success' => true]);
	}

	public function deleteNotifications(\Illuminate\Http\Request $request)
	{
		$candidature = auth('sanctum')->user() ?? auth('web_candidatures')->user();
		if (!$candidature) return response()->json(['message' => 'Non autorisé'], 401);

		$ids = $request->input('ids', []);
		if (!empty($ids)) {
			$candidature->notifications()->whereIn('id', $ids)->delete();
		}

		return response()->json(['success' => true]);
	}
}
