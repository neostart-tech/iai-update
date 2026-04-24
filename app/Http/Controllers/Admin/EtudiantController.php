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
                'album',
                'tuteur',
                'responsable'
            ])
            ->firstOrFail(); // renvoie 404 si non trouvé

        return new EtudiantRessource($etudiant);
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
            'matricule' => 'required|string|unique:etudiants,matricule,' . $etudiant->id,
            'promotion' => 'nullable|string|max:100',
            'annee_admission' => 'required|string|max:4',
            'nom_jeune_fille' => 'nullable|string|max:255',
            'biographie' => 'nullable|string',
            'mode_formation' => 'required|string',
            // Tuteur
            'tuteur.nom' => 'nullable|string|max:255',
            'tuteur.prenom' => 'nullable|string|max:255',
            'tuteur.tel' => 'nullable|string|max:20',
            'tuteur.email' => 'nullable|email',
            'tuteur.profession' => 'nullable|string|max:255',
            'tuteur.adresse' => 'nullable|string|max:255',
            // Responsable
            'responsable.nom' => 'nullable|string|max:255',
            'responsable.prenom' => 'nullable|string|max:255',
            'responsable.tel' => 'nullable|string|max:20',
            'responsable.email' => 'nullable|email',
            'responsable.profession' => 'nullable|string|max:255',
            'responsable.adresse' => 'nullable|string|max:255',
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

        // Tuteur
        if ($request->has('tuteur')) {
            $etudiant->tuteur()->updateOrCreate(
                ['owner_id' => $etudiant->id, 'owner_type' => get_class($etudiant)],
                $request->input('tuteur')
            );
        }

        // Responsable
        if ($request->has('responsable')) {
            $etudiant->responsable()->updateOrCreate(
                ['owner_id' => $etudiant->id, 'owner_type' => get_class($etudiant)],
                $request->input('responsable')
            );
        }

        // Mettre à jour le groupe si nécessaire
        $etudiantGroup = $etudiant->etudiantGroups()
            ->where('annee_scolaire_id', $anneeActiveId)
            ->first();

        if ($etudiantGroup) {
            // Vérifier que le nouveau groupe appartient au même niveau
            $newGroup = Group::findOrFail($request->group_id);
            if ($newGroup->niveau_id !== $etudiantGroup->niveau_id) {
                return response()->json([
                    'message' => 'Le nouveau groupe doit appartenir au même niveau.'
                ], 422);
            }

            $etudiantGroup->update([
                'group_id' => $request->group_id,
                'mode_formation' => $request->mode_formation
            ]);
        }

        // Mettre à jour l'album (fichiers)
        $this->updateEtudiantAlbum($request, $etudiant);

        return new EtudiantRessource($etudiant->load(['etudiantGroups.group', 'etudiantGroups.filiere', 'etudiantGroups.niveau', 'tuteur', 'responsable', 'album']));
    }

    private function updateEtudiantAlbum(Request $request, Etudiant $etudiant)
    {
        $filePrefix = Str::slug($etudiant->nom . '_' . $etudiant->prenom);
        $album = $etudiant->album;
        $data = [];

        // Fichiers uniques
        $fileFields = [
            'lettre_file' => 'lettre',
            'naissance_file' => 'naissance',
            'diplome_file' => 'diplome',
            'nationalite_file' => 'nationalite',
            'photo_identite_file' => 'photo',
            'certificat_medical_file' => 'certificat_medical',
            'coupon_file' => 'coupon',
            'cv_file' => 'cv'
        ];

        foreach ($fileFields as $requestKey => $dbKey) {
            if ($request->hasFile($requestKey)) {
                $folder = match ($requestKey) {
                    'lettre_file' => static::LETTRE,
                    'naissance_file' => static::NAISSANCE,
                    'diplome_file' => static::DIPLOME,
                    'nationalite_file' => static::NATIONALITE,
                    'photo_identite_file' => static::PHOTO_IDENTITE,
                    'certificat_medical_file' => static::CERTIFICAT_MEDICAL,
                    'coupon_file' => static::COUPON,
                    'cv_file' => 'cv',
                    default => 'others'
                };
                $data[$dbKey] = $this->storeFile($request, $requestKey, $folder, $filePrefix);
            }
        }

        // Bulletins
        $allBulletins = [];
        if ($album && $album->bulletins_lycee_paths) {
            $allBulletins = json_decode($album->bulletins_lycee_paths, true) ?: [];
        }

        $hasNewBulletins = false;
        foreach (['seconde', 'premiere', 'terminale'] as $niveau) {
            if ($request->hasFile("bulletins_{$niveau}")) {
                $paths = $this->storeMultipleFiles($request, "bulletins_{$niveau}", 'bulletins', $niveau, $filePrefix);
                $allBulletins[$niveau] = $paths;
                $hasNewBulletins = true;
            }
        }
        if ($hasNewBulletins) {
            $data['bulletins_lycee_paths'] = json_encode($allBulletins);
        }

        // Relevés
        if ($request->hasFile("releve_bac1")) {
            $bac1Paths = $this->storeMultipleFiles($request, "releve_bac1", 'releves', 'bac1', $filePrefix);
            if (!empty($bac1Paths)) $data['releve_bac1_path'] = $bac1Paths[0];
        }
        if ($request->hasFile("releve_bac2")) {
            $bac2Paths = $this->storeMultipleFiles($request, "releve_bac2", 'releves', 'bac2', $filePrefix);
            if (!empty($bac2Paths)) $data['releve_bac2_path'] = $bac2Paths[0];
        }

        if ($album) {
            $album->update($data);
        } else {
            $etudiant->album()->create($data);
        }
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
