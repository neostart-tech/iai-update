<?php

namespace App\Http\Controllers;

use App\Jobs\ApplyStudentToAnnouncementJob;
use App\Models\Album;
use App\Models\Announcement;
use App\Models\AnnouncementEtudiant;
use App\Models\Etudiant;
use App\Models\Group;
use App\Models\Paiement;
use App\Models\Candidature;
use App\Models\AnneeScolaire;
use App\Notifications\Etudiants\{EtudiantGroupeUpdateNotification};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class EtudiantController extends Controller
{

	public function index()
	{
		//
	}

	public function create()
	{
		//
	}

	public function store(Request $request)
	{
		//
	}

	public function show(Etudiant $etudiant): View
	{
		$etudiant->load('etudiantGroups.filiere', 'etudiantGroups.niveau', 'etudiantGroups.group');
		return view('admin.etudiants.show', compact('etudiant'));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Etudiant $etudiant)
	{
		//
	}

	public function getProfilCompletion()
	{
		/** @var \App\Models\Etudiant $etudiant */
		$etudiant = auth()->user();
		if (!$etudiant || !($etudiant instanceof \App\Models\Etudiant)) {
			return response()->json(['message' => 'Non autorisé.'], 403);
		}

		$completion = $etudiant->checkProfilCompletion();

		// Charger les exigences complètes de son niveau/filière
		$anneeActiveId = function_exists('injectAnneeScolaireId') ? injectAnneeScolaireId() : null;
		if (is_array($anneeActiveId)) {
			$anneeActiveId = $anneeActiveId['annee_scolaire_id'] ?? null;
		}
		if (!$anneeActiveId && function_exists('getAnneeScolaireId')) {
			$anneeActiveId = getAnneeScolaireId();
		}

		$dernierGroupe = $etudiant->allEtudiantGroups()
			->when($anneeActiveId, function ($q) use ($anneeActiveId) {
				$q->where('annee_scolaire_id', $anneeActiveId);
			})
			->latest('id')
			->first();

		$requirements = collect();
		if ($dernierGroupe && $dernierGroupe->niveau_id) {
			$requirements = \App\Models\DocumentRequirement::with('documentType')
				->where('niveau_id', $dernierGroupe->niveau_id)
				->where(function ($q) use ($dernierGroupe) {
					$q->whereNull('filiere_id');
					if ($dernierGroupe->filiere_id) {
						$q->orWhere('filiere_id', $dernierGroupe->filiere_id);
					}
				})
				->get()
				->map(function ($req) {
					return [
						'id' => $req->id,
						'document_type_id' => $req->document_type_id,
						'document_key' => $req->documentType->document_key ?? '',
						'nom_affichage' => $req->documentType->nom_affichage ?? '',
						'description' => $req->description,
						'is_obligatoire' => (bool) $req->is_obligatoire,
						'is_photo' => (bool) ($req->documentType->is_photo ?? false),
						'is_multiple' => (bool) ($req->documentType->is_multiple ?? false),
						'accepted_formats' => $req->documentType->accepted_formats ?? 'all',
					];
				});
		}

		$submittedDocs = $etudiant->submittedDocuments()
			->get()
			->map(function ($doc) {
				return [
					'id' => $doc->id,
					'document_key' => $doc->document_key,
					'file_path' => asset(\Illuminate\Support\Facades\Storage::url($doc->file_path)),
					'statut' => $doc->statut,
				];
			});

		return response()->json([
			'est_complet' => $completion['est_complet'],
			'champs_manquants' => $completion['champs_manquants'],
			'documents_manquants' => $completion['documents_manquants'],
			'etudiant' => [
				'id' => $etudiant->id,
				'matricule' => $etudiant->matricule,
				'nom' => $etudiant->nom,
				'prenom' => $etudiant->prenom,
				'nom_jeune_fille' => $etudiant->nom_jeune_fille,
				'genre' => $etudiant->genre?->value ?? $etudiant->genre,
				'date_naissance' => $etudiant->date_naissance?->format('Y-m-d'),
				'lieu_naissance' => $etudiant->lieu_naissance,
				'nationalite' => $etudiant->nationalite,
				'tel' => $etudiant->tel,
				'email' => $etudiant->email,
				'biographie' => $etudiant->biographie,
				'photo_url' => $etudiant->image ? asset(\Illuminate\Support\Facades\Storage::url($etudiant->image)) : null,
			],
			'tuteur' => $etudiant->tuteur ? [
				'id' => $etudiant->tuteur->id,
				'nom' => $etudiant->tuteur->nom,
				'prenom' => $etudiant->tuteur->prenom,
				'profession' => $etudiant->tuteur->profession,
				'employeur' => $etudiant->tuteur->employeur,
				'email' => $etudiant->tuteur->email,
				'tel' => $etudiant->tuteur->tel,
				'adresse' => $etudiant->tuteur->adresse,
			] : null,
			'responsable' => $etudiant->responsable ? [
				'id' => $etudiant->responsable->id,
				'nom' => $etudiant->responsable->nom,
				'prenom' => $etudiant->responsable->prenom,
				'profession' => $etudiant->responsable->profession,
				'employeur' => $etudiant->responsable->employeur,
				'email' => $etudiant->responsable->email,
				'tel' => $etudiant->responsable->tel,
				'adresse' => $etudiant->responsable->adresse,
			] : null,
			'documents_requis' => $requirements,
			'documents_soumis' => $submittedDocs,
			'profil_verifie_at' => $etudiant->profil_verifie_at?->format('Y-m-d H:i:s'),
			'derniere_demande' => ($derniereDemande = \App\Models\EtudiantProfileRequest::where('etudiant_id', $etudiant->id)->latest()->first()) ? [
				'id' => $derniereDemande->id,
				'statut' => $derniereDemande->statut,
				'motif_refus' => $derniereDemande->motif_refus,
				'created_at' => $derniereDemande->created_at?->format('d/m/Y H:i'),
				'data' => $derniereDemande->data,
				'documents' => $derniereDemande->documents,
			] : null,
		]);
	}

	public function confirmerProfilCorrect(Request $request)
	{
		/** @var \App\Models\Etudiant $etudiant */
		$etudiant = auth()->user();
		if (!$etudiant || !($etudiant instanceof \App\Models\Etudiant)) {
			return response()->json(['message' => 'Non autorisé.'], 403);
		}

		$pending = \App\Models\EtudiantProfileRequest::where('etudiant_id', $etudiant->id)
			->where('statut', 'en_attente')
			->first();
		if ($pending) {
			return response()->json([
				'message' => 'Une demande de modification de votre profil est déjà en cours de validation par l\'administration.',
			], 422);
		}

		$etudiant->update([
			'profil_verifie_at' => now(),
		]);

		return response()->json([
			'message' => 'Merci ! Votre profil a été confirmé avec succès.',
			'user' => $etudiant->fresh(),
		]);
	}

	public function updateMonProfil(Request $request)
	{
		/** @var \App\Models\Etudiant $etudiant */
		$etudiant = auth()->user();
		if (!$etudiant || !($etudiant instanceof \App\Models\Etudiant)) {
			return response()->json(['message' => 'Non autorisé.'], 403);
		}

		$pending = \App\Models\EtudiantProfileRequest::where('etudiant_id', $etudiant->id)
			->where('statut', 'en_attente')
			->first();
		if ($pending) {
			return response()->json([
				'message' => 'Une demande de modification de votre profil est déjà en cours de validation par l\'administration.',
			], 422);
		}

		$request->validate([
			'nom' => 'required|string|max:255',
			'prenom' => 'required|string|max:255',
			'nom_jeune_fille' => 'nullable|string|max:255',
			'genre' => 'required|string',
			'date_naissance' => 'required|date|before:today',
			'lieu_naissance' => 'required|string|max:255',
			'nationalite' => 'required|string|max:100',
			'tel' => 'required|string|max:30',
			'biographie' => 'nullable|string|max:1000',

			// Tuteur
			'tuteur_nom' => 'required|string|max:255',
			'tuteur_prenom' => 'required|string|max:255',
			'tuteur_tel' => 'required|string|max:30',
			'tuteur_email' => 'nullable|email|max:255',
			'tuteur_profession' => 'nullable|string|max:255',
			'tuteur_adresse' => 'nullable|string|max:255',

			// Responsable financier
			'meme_responsable' => 'nullable',
			'responsable_nom' => 'nullable|string|max:255',
			'responsable_prenom' => 'nullable|string|max:255',
			'responsable_tel' => 'nullable|string|max:30',
			'responsable_email' => 'nullable|email|max:255',
			'responsable_adresse' => 'nullable|string|max:255',

			// Fichiers dynamiques
			'documents' => 'nullable|array',
			'documents.*' => 'nullable|file|max:10240', // 10MB max
		]);

		return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $etudiant) {
			// 1. Stocker les fichiers soumis temporairement dans les requêtes
			$uploadedDocs = [];
			if ($request->hasFile('documents')) {
				foreach ($request->file('documents') as $docKey => $file) {
					if ($file && $file->isValid()) {
						$path = $file->store('etudiants/documents/requests', 'public');
						$uploadedDocs[$docKey] = $path;
					}
				}
			}

			// 2. Structurer les données soumises (sans modifier email et matricule qui restent verrouillés)
			$profileData = [
				'nom' => $request->nom,
				'prenom' => $request->prenom,
				'nom_jeune_fille' => $request->nom_jeune_fille,
				'genre' => $request->genre,
				'date_naissance' => $request->date_naissance,
				'lieu_naissance' => $request->lieu_naissance,
				'nationalite' => $request->nationalite,
				'tel' => $request->tel,
				'biographie' => $request->biographie,
				'email' => $etudiant->email, // Verrouillé
				'matricule' => $etudiant->matricule, // Verrouillé
				'tuteur' => [
					'nom' => $request->tuteur_nom,
					'prenom' => $request->tuteur_prenom,
					'tel' => $request->tuteur_tel,
					'email' => $request->tuteur_email,
					'profession' => $request->tuteur_profession,
					'adresse' => $request->tuteur_adresse,
				],
				'meme_responsable' => (bool) $request->boolean('meme_responsable', true),
				'responsable' => $request->boolean('meme_responsable', true) ? [
					'nom' => $request->tuteur_nom,
					'prenom' => $request->tuteur_prenom,
					'tel' => $request->tuteur_tel,
					'email' => $request->tuteur_email,
					'profession' => $request->tuteur_profession,
					'adresse' => $request->tuteur_adresse,
				] : [
					'nom' => $request->responsable_nom,
					'prenom' => $request->responsable_prenom,
					'tel' => $request->responsable_tel,
					'email' => $request->responsable_email,
					'adresse' => $request->responsable_adresse,
				],
			];

			// 3. Créer la demande de profil
			$profileRequest = \App\Models\EtudiantProfileRequest::create([
				'etudiant_id' => $etudiant->id,
				'data' => $profileData,
				'documents' => $uploadedDocs,
				'statut' => 'en_attente',
			]);

			// 4. Marquer le profil comme vérifié par l'étudiant pour ne JAMAIS le bloquer dans sa navigation
			$etudiant->update([
				'profil_verifie_at' => now(),
			]);

			// 5. Notifier par mail les utilisateurs ayant la permission 'valider-profil-etudiant'
			try {
				$validators = \App\Models\User::whereHas('roles.permissions', function ($q) {
					$q->where('slug', 'valider-profil-etudiant');
				})->get();

				if ($validators->isNotEmpty()) {
					\Illuminate\Support\Facades\Notification::send(
						$validators,
						new \App\Notifications\Etudiants\EtudiantProfileSubmittedNotification($etudiant, $profileRequest)
					);
				}
			} catch (\Throwable $e) {
				\Illuminate\Support\Facades\Log::error('Erreur notification validation profil: ' . $e->getMessage());
			}

			return response()->json([
				'message' => 'Votre profil a été enregistré. Vos modifications ont été soumises et sont en attente de validation par l\'administration.',
				'demande' => $profileRequest,
				'user' => $etudiant->fresh(),
			]);
		});
	}

	public function myFiles(): View
	{

		$album = Album::query()->where('owner_id', auth()->user()->id)->first();

		return view('etudiants.my-space.files')->with([
			'album' => $album
		]);
	}

	public function constitution()
	{
		$etudiant = auth()->user()->load(['etudiantGroups.group','etudiantGroups.niveau','etudiantGroups.filiere']);

		return view('etudiants.my-space.constitution', compact('etudiant'));
	}


	public function changeGroup(Request $request, Etudiant $etudiant): RedirectResponse
	{
		$request->validate([
			'group_id' => ['required', 'exists:groups,slug']
		]);

		$etudiant->group()->first()->pivot->update([
			'group_id' => ($group =
				Group::query()->firstWhere('slug', $request->input('group_id'))
			)->getAttribute('id')
		]);
		successMsg("Décision appliquée avec succès");

		$message = $etudiant->greeting();
		$message .= ". Vous avez été affecté dans le groupe " . $group->getAttribute('nom') . ".";

		$etudiant->notify(new EtudiantGroupeUpdateNotification($message));
		return back();
	}

	public function myAccount(): View
	{
		return view('etudiants.my-space.my-account');
	}

	public function myPayment()
	{
		$etudiant = auth('etudiants')->user();
		$paiements = Paiement::where('etudiant_id', $etudiant->id)
			->with(['tranchePaiement.anneeScolaire'])
			->where('annule', false)
			->orderByDesc('date_paiement')
			->get();

		$paiementsParAnnee = $paiements->groupBy(function ($paiement) {
			return $paiement->tranchePaiement && $paiement->tranchePaiement->anneeScolaire
				? $paiement->tranchePaiement->anneeScolaire->nom
				: 'Année inconnue';
		});

		$candidature = Candidature::where('etudiant_id', $etudiant->id)->latest()->first();

		$fraisScolairite = $candidature->niveau->fraisScolarites[0]->montant;
		$paiementTotal = Paiement::where('etudiant_id', $candidature->etudiant_id)
			->where('annule', false)
			->get()->sum('montant');

		$Ajour = false;

		if ($fraisScolairite == $paiementTotal) {
			$Ajour = true;
		}

		return view('etudiants.my-space.my-payment', compact('paiements', 'paiementsParAnnee', 'etudiant', 'Ajour'));
	}
}
