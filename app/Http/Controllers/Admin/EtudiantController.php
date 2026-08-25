<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EtudiantRessource;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Group;
use App\Models\Niveau;
use App\Imports\EtudiantsImport;
use App\Traits\FileManagementTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;


class EtudiantController extends Controller
{
    use FileManagementTrait;

    private const LETTRE = 'lettres_manuscrites';
    private const NAISSANCE = 'naissances';
    private const NATIONALITE = 'nationalites';
    private const DIPLOME = 'diplomes';
    private const PHOTO_IDENTITE = 'photos_identite';
    private const CERTIFICAT_MEDICAL = 'certificats_medicaux';
    private const COUPON = 'coupons';
    public function index(Request $request)
    {
        $anneeActiveId = injectAnneeScolaireId();

        $etudiants = Etudiant::whereHas('etudiantGroups', function ($q) use ($anneeActiveId, $request) {

            $q->where('annee_scolaire_id', $anneeActiveId);
        })->with([
            'etudiantGroups' => function ($q) use ($anneeActiveId) {
                $q->where('annee_scolaire_id', $anneeActiveId)
                    ->latest('id');
            },
            'etudiantGroups.group',
            'etudiantGroups.filiere',
            'etudiantGroups.niveau',
            'submittedDocuments',
            'fraisEtudiant',
        ])->orderBy('nom')->orderBy('prenom')->get();

        return EtudiantRessource::collection($etudiants);

        // return view('admin.etudiants._index', [
        //     'etudiants' => $etudiants,
        //     'groupes'   => Group::with('niveau')->get(),
        //     'groups'   => Group::with('niveau')->get(),
        //     'filieres'  => Filiere::all(),
        //     'niveaux'   => Niveau::all(),
        // ]);
    }

    public function getNonBoursiers()
    {
        $anneeActiveId = injectAnneeScolaireId();

        // Récupérer les IDs des étudiants qui ont déjà une bourse pour l'année active
        $etudiantsAvecBourseIds = DB::table('bourse_etudiants')
            ->where('annee_scolaire_id', $anneeActiveId)
            ->pluck('etudiant_id')
            ->toArray();

        // Récupérer uniquement les étudiants SANS bourse pour l'année active
        $etudiants = Etudiant::whereHas('etudiantGroups', function ($q) use ($anneeActiveId) {
            $q->where('annee_scolaire_id', $anneeActiveId);
        })
            ->whereNotIn('id', $etudiantsAvecBourseIds)
            ->with([
                'etudiantGroups' => function ($q) use ($anneeActiveId) {
                    $q->where('annee_scolaire_id', $anneeActiveId)
                        ->latest('id');
                },
                'etudiantGroups.group',
                'etudiantGroups.filiere',
                'etudiantGroups.niveau',
            ])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return EtudiantRessource::collection($etudiants);
    }


    public function show(Etudiant $etudiant)
    {
        $anneeActiveId = injectAnneeScolaireId();

        // Récupérer l'étudiant avec ses groupes, filière et niveau
        $etudiant = Etudiant::where('id', $etudiant->id)
            ->with([
                'etudiantGroups' => function ($q) use ($anneeActiveId) {
                    $q->where('annee_scolaire_id', $anneeActiveId)
                        ->latest('id');
                },
                'etudiantGroups.group',
                'etudiantGroups.filiere',
                'etudiantGroups.niveau',
                'tuteurs',
                'responsables',
                'submittedDocuments'
            ])
            ->firstOrFail(); // renvoie 404 si non trouvé

        return new EtudiantRessource($etudiant);
    }


    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'matricule' => 'required|string|unique:etudiants,matricule',
            'filiere_id' => 'required|exists:filieres,id',
            'niveau_id' => 'required|exists:niveaux,id',
            'group_id' => 'required|exists:groups,id',
            'mode_formation' => 'nullable|string',
            'email' => 'required|email|unique:etudiants,email',
            'tel' => 'nullable|string|max:20',
            'genre' => 'nullable|string',
            'nationalite' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date',
            'document' => 'nullable|array',
            'document.*' => 'nullable|file',
            'document_requirement' => 'nullable|array',
            'document_requirement.*' => 'nullable|exists:document_requirements,id',
        ]);

        $anneeScolaireId = $request->annee_scolaire_id ?: getAnneeScolaireId();

        return DB::transaction(function () use ($request, $anneeScolaireId) {
            // 1. Définir l'email
            $email = $request->email;
            if (empty($email)) {
                $prenomClean = strtolower(preg_replace('/[^a-z0-9]/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $request->prenom)));
                $nomClean = strtolower(preg_replace('/[^a-z0-9]/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $request->nom)));
                $email = $prenomClean . '.' . $nomClean . '.' . $request->matricule . '@etudiant.exemple.com';
            }

            // 2. Créer l'étudiant
            $etudiant = Etudiant::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'matricule' => $request->matricule,
                'email' => $email,
                'tel' => $request->tel,
                'genre' => $request->genre ?? 'Féminin',
                'nationalite' => $request->nationalite,
                'date_naissance' => $request->date_naissance,
                'annee_admission' => now()->year,
                'slug' => (string) Str::uuid(),
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]);

            // 3. Assigner le rôle étudiant
            $roleEtudiantId = \App\Models\Role::where('slug', 'etudiant')->value('id');
            if ($roleEtudiantId) {
                $etudiant->roles()->attach($roleEtudiantId);
            }

            // 3b. Tuteur et Responsable
            if ($request->filled('nom_tuteur') || $request->filled('prenom_tuteur')) {
                $etudiant->tuteur()->create([
                    'nom' => $request->nom_tuteur,
                    'prenom' => $request->prenom_tuteur ?? $request->nom_tuteur, // Fallback
                    'tel' => $request->contact_tuteur,
                    'profession' => $request->profession_tuteur,
                    'email' => $request->email_tuteur,
                    'adresse' => $request->adresse_tuteur,
                ]);
            }

            if ($request->filled('nom_responsable') || $request->filled('prenom_responsable')) {
                $etudiant->responsable()->create([
                    'nom' => $request->nom_responsable,
                    'prenom' => $request->prenom_responsable ?? $request->nom_responsable, // Fallback
                    'tel' => $request->contact_responsable,
                    'profession' => $request->profession_responsable,
                    'email' => $request->email_responsable,
                    'adresse' => $request->adresse_responsable,
                ]);
            }

            // 4. Trouver le groupe correspondant
            $groupId = $request->group_id;

            // 5. Liaison avec le groupe/filière via Eloquent
            if ($groupId) {
                $etudiant->groups()->attach($groupId, [
                    'filiere_id' => $request->filiere_id,
                    'niveau_id' => $request->niveau_id,
                    'annee_scolaire_id' => $anneeScolaireId,
                    'mode_formation' => $request->mode_formation ?? 'Présentiel',
                ]);
            }

            // 6. Frais d'inscription automatique
            $fraisInscriptionActif = \App\Models\FraisInscription::where('active', true)
                ->where('annee_scolaire_id', $anneeScolaireId)
                ->first();
            
            if ($fraisInscriptionActif) {
                DB::table('paiements')->insert([
                    'etudiant_id'   => $etudiant->id,
                    'montant'       => $fraisInscriptionActif->montant,
                    'mode_paiement' => 'especes',
                    'nature_paiement' => 'inscription',
                    'reference'     => 'INS-' . $etudiant->matricule . '-' . time(),
                    'status'        => 'valide',
                    'payable_type'  => 'App\\Models\\FraisInscription',
                    'payable_id'    => $fraisInscriptionActif->id,
                    'date_paiement' => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 7. Enregistrement des documents fournis
            if ($request->hasFile('document')) {
                $documents = $request->file('document');
                $requirementIds = $request->input('document_requirement', []);

                foreach ($documents as $index => $file) {
                    $reqId = $requirementIds[$index] ?? null;
                    if ($reqId) {
                        $requirement = \App\Models\DocumentRequirement::find($reqId);
                        if ($requirement) {
                            $path = $file->store('etudiants/documents', 'public');
                            $etudiant->submittedDocuments()->create([
                                'document_key' => $requirement->documentType->document_key ?? 'inconnu',
                                'file_path' => $path,
                                'statut' => 'valide',
                            ]);
                        }
                    }
                }
            }

            // 8. Attribuer les frais de scolarité par défaut
            $etudiantComplet = Etudiant::with(['etudiantGroups' => function($q) use ($anneeScolaireId) {
                $q->where('annee_scolaire_id', $anneeScolaireId);
            }])->find($etudiant->id);

            $fraisService = new \App\Services\FraisEtudiantService();
            $fraisService->assignDefaultFrais($etudiantComplet, $anneeScolaireId);

            // 8. Envoyer l'email de bienvenue avec les identifiants
            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(
                    new \App\Mail\Etudiants\WelcomeEtudiantMail(
                        'Bonjour ' . $etudiantComplet->prenom,
                        $email,
                        'password'
                    )
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur envoi email étudiant (Admin): " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Étudiant enregistré avec succès',
                'etudiant' => new EtudiantRessource($etudiantComplet->load(['etudiantGroups.group', 'etudiantGroups.filiere', 'etudiantGroups.niveau']))
            ]);
        });
    }

    // public function importEtudiant(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xls,xlsx'
    //     ]);

    //     Excel::import(
    //         new EtudiantsImport,
    //         $request->file('file')
    //     );

    //     return back()->with(
    //         'success',
    //         'Importation réussie avec succès'
    //     );
    // }



    public function update(Request $request, Etudiant $etudiant)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:etudiants,email,' . $etudiant->id,
            'tel' => 'nullable|string|max:20',
            'genre' => 'required|string',
            'nationalite' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'group_id' => 'required|exists:groups,id',
            'filiere_id' => 'nullable|exists:filieres,id',
            'niveau_id' => 'nullable|exists:niveaux,id',
            'matricule' => 'required|string|unique:etudiants,matricule,' . $etudiant->id,
            'promotion' => 'nullable|string|max:100',
            'annee_admission' => 'required|string|max:4',
            'nom_jeune_fille' => 'nullable|string|max:255',
            'biographie' => 'nullable|string',
            'mode_formation' => 'required|string',
            // Tuteurs
            'tuteurs' => 'nullable|array',
            'tuteurs.*.nom' => 'nullable|string|max:255',
            'tuteurs.*.prenom' => 'nullable|string|max:255',
            'tuteurs.*.tel' => 'nullable|string|max:20',
            'tuteurs.*.email' => 'nullable|email',
            'tuteurs.*.profession' => 'nullable|string|max:255',
            'tuteurs.*.adresse' => 'nullable|string|max:255',
            // Responsables
            'responsables' => 'nullable|array',
            'responsables.*.nom' => 'nullable|string|max:255',
            'responsables.*.prenom' => 'nullable|string|max:255',
            'responsables.*.tel' => 'nullable|string|max:20',
            'responsables.*.email' => 'nullable|email',
            'responsables.*.profession' => 'nullable|string|max:255',
            'responsables.*.adresse' => 'nullable|string|max:255',
            // Fichiers
            'photo_identite_file' => 'nullable|file|image|max:2048',
            'naissance_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'diplome_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nationalite_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'certificat_medical_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'lettre_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'coupon_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'bulletins_seconde.*' => 'nullable|file|mimes:pdf|max:2048',
            'bulletins_premiere.*' => 'nullable|file|mimes:pdf|max:2048',
            'bulletins_terminale.*' => 'nullable|file|mimes:pdf|max:2048',
            'releve_bac1.*' => 'nullable|file|mimes:pdf|max:2048',
            'releve_bac2.*' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $anneeActiveId = injectAnneeScolaireId();

        // Mettre à jour les infos personnelles
        $updateData = $request->only([
            'nom', 'prenom', 'email', 'tel', 'genre', 
            'nationalite', 'date_naissance', 'lieu_naissance',
            'biographie', 'nom_jeune_fille', 'matricule', 
            'promotion', 'annee_admission'
        ]);

        // Filtrer les valeurs nulles pour les champs qui sont obligatoires en base de données
        // (En attendant l'exécution de la migration rendant ces champs facultatifs)
        foreach (['tel', 'nationalite', 'lieu_naissance', 'date_naissance'] as $field) {
            if (array_key_exists($field, $updateData) && is_null($updateData[$field])) {
                unset($updateData[$field]);
            }
        }

        $etudiant->update($updateData);

        // Tuteurs
        if ($request->has('tuteurs')) {
            $etudiant->tuteurs()->delete();
            foreach ($request->input('tuteurs') as $tuteurData) {
                if (!empty($tuteurData['nom'])) {
                    $etudiant->tuteurs()->create($tuteurData);
                }
            }
        }

        // Responsables
        if ($request->has('responsables')) {
            $etudiant->responsables()->delete();
            foreach ($request->input('responsables') as $respData) {
                if (!empty($respData['nom'])) {
                    $etudiant->responsables()->create($respData);
                }
            }
        }

        // Mettre à jour le groupe si nécessaire
        $etudiantGroup = $etudiant->etudiantGroups()
            ->where('annee_scolaire_id', $anneeActiveId)
            ->first();

        $financialImpact = false;

        // Detecter si le genre a change (peut impacter le tarif)
        if ($etudiant->wasChanged('genre')) {
            $financialImpact = true;
        }

        if ($etudiantGroup) {
            $newGroup = Group::findOrFail($request->group_id);
            $newNiveauId = $request->niveau_id ?? $newGroup->niveau_id;
            $newFiliereId = $request->filiere_id ?? $newGroup->filiere_id ?? $etudiantGroup->filiere_id;

            if (
                $etudiantGroup->group_id != $request->group_id ||
                $etudiantGroup->niveau_id != $newNiveauId ||
                $etudiantGroup->filiere_id != $newFiliereId ||
                $etudiantGroup->mode_formation != $request->mode_formation
            ) {
                $financialImpact = true;
            }

            $etudiantGroup->update([
                'group_id' => $request->group_id,
                'niveau_id' => $newNiveauId,
                'filiere_id' => $newFiliereId,
                'mode_formation' => $request->mode_formation
            ]);
        } else {
            $newGroup = Group::findOrFail($request->group_id);
            $newNiveauId = $request->niveau_id ?? $newGroup->niveau_id;
            $newFiliereId = $request->filiere_id ?? $newGroup->filiere_id;

            $etudiant->etudiantGroups()->create([
                'annee_scolaire_id' => $anneeActiveId,
                'group_id' => $request->group_id,
                'niveau_id' => $newNiveauId,
                'filiere_id' => $newFiliereId,
                'mode_formation' => $request->mode_formation
            ]);
            $financialImpact = true;
        }

        // Synchroniser les frais si un changement financier est detecte
        if ($financialImpact) {
            try {
                $fraisService = new \App\Services\FraisEtudiantService();
                $fraisService->synchroniserApresModificationProfil($etudiant, $anneeActiveId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur sync frais apres modif profil: " . $e->getMessage());
            }
        }

        // Mettre à jour l'album (fichiers)
        $this->updateEtudiantAlbum($request, $etudiant);

        return response()->json([
            'data' => new EtudiantRessource($etudiant->load(['etudiantGroups.group', 'etudiantGroups.filiere', 'etudiantGroups.niveau', 'tuteurs', 'responsables', 'submittedDocuments'])),
            'financial_impact' => $financialImpact
        ]);
    }

    private function updateEtudiantAlbum(Request $request, Etudiant $etudiant)
    {
        $etudiantGroup = $etudiant->etudiantGroups()->first();
        if (!$etudiantGroup) return;

        $requirements = \App\Models\DocumentRequirement::with('documentType')
            ->where('niveau_id', $etudiantGroup->niveau_id)
            ->where(function ($q) use ($etudiantGroup) {
                $q->whereNull('filiere_id')->orWhere('filiere_id', $etudiantGroup->filiere_id);
            })->get();

        $filePrefix = Str::slug($etudiant->nom . '_' . $etudiant->prenom);

		$mapKeyForUpload = function ($dbKey) {
			$map = [
				'lettre' => 'lettre_file',
				'naissance' => 'naissance_file',
				'diplome' => 'diplome_file',
				'nationalite' => 'nationalite_file',
				'photo' => 'photo_identite_file',
				'certificat_medical' => 'certificat_medical_file',
				'cv_path' => 'cv_file',
				'cv' => 'cv_file',
				'coupon' => 'coupon_file',
				'releve_bac1_path' => 'releve_bac1',
				'releve_bac2_path' => 'releve_bac2',
			];
			return $map[$dbKey] ?? $dbKey;
		};

        // 1. Validation dynamique des formats
        $rules = [];
        $messages = [];

        foreach ($requirements as $req) {
            $type = $req->documentType;
            if (!$type) continue;
            
            $docKey = $type->document_key;
            $requestKey = $mapKeyForUpload($docKey);
            $actualKey = null;
			if ($request->hasFile($requestKey)) {
				$actualKey = $requestKey;
			} elseif ($request->hasFile($docKey)) {
				$actualKey = $docKey;
			} elseif ($request->hasFile($docKey . '_file')) {
				$actualKey = $docKey . '_file';
			}

            if ($actualKey && $request->hasFile($actualKey)) {
                if ($type->accepted_formats === 'image') {
                    $rules[$actualKey] = 'image|mimes:jpeg,png,jpg,gif,webp';
                    $rules["{$actualKey}.*"] = 'image|mimes:jpeg,png,jpg,gif,webp';
                    $messages["{$actualKey}.image"] = "Le document {$type->nom_affichage} doit être une image.";
                    $messages["{$actualKey}.mimes"] = "Le document {$type->nom_affichage} doit être au format jpeg, png, jpg, gif ou webp.";
                } elseif ($type->accepted_formats === 'pdf') {
                    $rules[$actualKey] = 'mimes:pdf';
                    $rules["{$actualKey}.*"] = 'mimes:pdf';
                    $messages["{$actualKey}.mimes"] = "Le document {$type->nom_affichage} doit être un fichier PDF.";
                }
            }
        }

        if (!empty($rules)) {
            \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages)->validate();
        }

		foreach ($requirements as $req) {
			$docKey = $req->documentType->document_key ?? null;
			if (!$docKey) continue;

			$requestKey = $mapKeyForUpload($docKey);
			$folder = 'documents/' . $docKey;

			$actualKey = null;
			if ($request->hasFile($requestKey)) {
				$actualKey = $requestKey;
			} elseif ($request->hasFile($docKey)) {
				$actualKey = $docKey;
			} elseif ($request->hasFile($docKey . '_file')) {
				$actualKey = $docKey . '_file';
			}

			if ($actualKey) {
				$isMultiple = in_array($actualKey, ['releve_bac1', 'releve_bac2']) || str_contains($actualKey, 'bulletins') || ($req->documentType->is_multiple ?? false);

				if ($isMultiple) {
					$paths = $this->storeMultipleFiles($request, $actualKey, $folder, 'files', $filePrefix);
					if (!empty($paths)) {
						$etudiant->submittedDocuments()->updateOrCreate(
							['document_key' => $docKey],
							['file_path' => json_encode($paths), 'statut' => 'en_attente']
						);
					}
				} else {
					$path = $this->storeFile($request, $actualKey, $folder, $filePrefix);
					$etudiant->submittedDocuments()->updateOrCreate(
						['document_key' => $docKey],
						['file_path' => $path, 'statut' => 'en_attente']
					);
				}
			}
		}
    }

    public function resetPassword(Request $request, Etudiant $etudiant)
    {
        $clearPassword = \Illuminate\Support\Str::random(10);

        $etudiant->update([
            'password' => \Illuminate\Support\Facades\Hash::make($clearPassword),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($etudiant->email)->send(new \App\Mail\Etudiants\StudentResetPasswordMail($etudiant, $clearPassword));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur d'envoi du mail de réinitialisation de mot de passe (Étudiant) : " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }

    public function destroy(Etudiant $etudiant)
    {
        // Au lieu de supprimer, on bascule le statut
        $nouveauStatut = $etudiant->statut === 'actif' ? 'inactif' : 'actif';
        
        $etudiant->update([
            'statut' => $nouveauStatut
        ]);

        $message = $nouveauStatut === 'inactif' 
            ? 'Étudiant désactivé avec succès' 
            : 'Étudiant réactivé avec succès';

        return response()->json([
            'message' => $message,
            'statut' => $nouveauStatut
        ]);
    }

    public function importEtudiant(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx'
        ]);

        (new EtudiantsImport)
            ->queue($request->file('file'))
            ->allOnQueue('imports'); // optionnel mais recommandé

        return response()->json([
            'status' => 'queued',
            'message' => 'Import lancé avec succès'
        ]);
    }
}
