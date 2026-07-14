<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Filiere;
use App\Models\Group;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use App\Models\Configuration;

class CarteEtudiantController extends Controller
{
    public function genererCarteEtudiant(Etudiant $etudiant)
    {
        $etudiant->load(['tuteur', 'etudiantGroups']);

        $group_id = EtudiantGroup::where('etudiant_id', $etudiant->id)->max('group_id');
        $group = Group::find($group_id);
        $group->load('niveau');
        $filiere = $etudiant->etudiantGroups->first()?->filiere?->nom ?? 'N/A';
        $niveau  = $etudiant->etudiantGroups->first()?->niveau?->libelle ?? 'N/A';

        $template = new TemplateProcessor(storage_path('app/templates/carte.docx'));
        $template->setValue('i', $etudiant->id);
        $template->setValue('nom', $etudiant->nom);
        $template->setValue('prenom', $etudiant->prenom);
        $template->setValue('datenaissance', date_format(date_create($etudiant->date_naissance), 'd/m/Y'));
        $template->setValue('lieunaissance', $etudiant->lieu_naissance);
        $template->setValue('matricule', $etudiant->matricule);
        $template->setValue('tel', $etudiant->tel);
        $template->setValue('genre', $etudiant->genre->value);
        $template->setValue('nationalite', $etudiant->nationalite);
        $template->setValue('tuteur', $etudiant->tuteur?->nom . ' ' . $etudiant->tuteur?->prenom);
        $template->setValue('filiere', $filiere);
        $template->setValue('niveau', $niveau);
        $template->setValue('anneescolaire', AnneeScolaire::where('active', true)->first()?->nom ?? '');

        $file = storage_path('app/carte_' . $etudiant->getNomCompletAttribute() . '.docx');
        $template->saveAs($file);

        return response()->file($file, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]);
    }

    /**
     * Retourne les données structurées pour la génération de cartes HTML/PDF (multi-sélection)
     * POST /student-cards/selected-data
     */
    public function selectedData(Request $request)
    {
        $ids = $request->input('ids', []);

        $etudiants = Etudiant::whereIn('id', $ids)
            ->with(['etudiantGroups' => function ($q) {
                $q->with(['filiere', 'niveau', 'anneeScolaire'])
                  ->orderBy('id', 'desc');
            }, 'submittedDocuments'])
            ->get();

        $anneeActive = AnneeScolaire::where('active', true)->first();
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');

        $photoKeys = \Illuminate\Support\Facades\Cache::remember('photo_document_keys', 3600, function () {
            return \App\Models\DocumentType::where('is_photo', true)->pluck('document_key')->toArray();
        });
        if (empty($photoKeys)) $photoKeys = ['photo', 'photo_identite'];

        $result = $etudiants->map(function ($etudiant) use ($anneeActive, $frontendUrl, $photoKeys) {
            $lastGroup = $etudiant->etudiantGroups->first();

            return [
                'id'          => $etudiant->id,
                'nom_complet' => $etudiant->getNomCompletAttribute(),
                'matricule'   => $etudiant->matricule,
                'filiere'     => $lastGroup?->filiere?->nom ?? 'N/A',
                'niveau'      => $lastGroup?->niveau?->libelle ?? 'N/A',
                'promotion'   => $anneeActive?->nom ?? date('Y') . '-' . (date('Y') + 1),
                'image_url'   => $etudiant->submittedDocuments->whereIn('document_key', $photoKeys)->first()?->file_path ? asset(\Illuminate\Support\Facades\Storage::url($etudiant->submittedDocuments->whereIn('document_key', $photoKeys)->first()->file_path)) : ($etudiant->image ? asset(\Illuminate\Support\Facades\Storage::url($etudiant->image)) : null),
                'qr_data'     => $frontendUrl . '/verif/' . $etudiant->matricule,
            ];
        });

        return response()->json($result);
    }

    /**
     * Génère un PDF groupé des cartes d'étudiants via dompdf
     * POST /student-cards/generate-pdf
     */
    public function genererCartesPdf(Request $request)
    {
        $ids = $request->input('ids', []);
        
        // Si c'est une chaîne (ex: ?ids=1,2,3), on la transforme en tableau
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return response()->json(['message' => 'Aucun étudiant sélectionné'], 400);
        }

        // 1. Récupérer les données (réutilisation de la logique existante)
        $etudiants = Etudiant::whereIn('id', $ids)
            ->with(['etudiantGroups' => function ($q) {
                $q->with(['filiere', 'niveau', 'anneeScolaire'])
                  ->orderBy('id', 'desc');
            }, 'submittedDocuments'])
            ->get();

        $anneeActive = AnneeScolaire::where('active', true)->first();
        $config = Configuration::first();
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');

        // 2. Préparer les données et convertir les images en base64 pour dompdf
        $cardsData = $etudiants->map(function ($etudiant) use ($anneeActive, $frontendUrl) {
            $lastGroup = $etudiant->etudiantGroups->first();
            
            // Conversion photo en base64
            $photoBase64 = null;
            $photoKeys = \Illuminate\Support\Facades\Cache::remember('photo_document_keys', 3600, function () {
            return \App\Models\DocumentType::where('is_photo', true)->pluck('document_key')->toArray();
        });
        if (empty($photoKeys)) $photoKeys = ['photo', 'photo_identite'];

        $photoDoc = $etudiant->submittedDocuments->whereIn('document_key', $photoKeys)->first();
            $photoPath = $photoDoc ? $photoDoc->file_path : $etudiant->image;
            
            if ($photoPath) {
                $possiblePaths = [
                    storage_path('app/public/' . $photoPath),
                    storage_path('app/public/photos_identite/' . $photoPath),
                    storage_path('app/public/photos_identite/' . basename($photoPath)),
                    public_path('storage/' . $photoPath),
                    public_path('storage/photos_identite/' . basename($photoPath)),
                ];

                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && is_file($path)) {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $photoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        break;
                    }
                }
            }
            
            if (!$photoBase64) {
                // Fallback avatar par défaut
                $defaultPath = public_path('images/default-avatar.png');
                if (file_exists($defaultPath)) {
                    $photoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultPath));
                }
            }

            return [
                'id'          => $etudiant->id,
                'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                'matricule'   => $etudiant->matricule,
                'filiere'     => $lastGroup?->filiere?->nom ?? 'N/A',
                'niveau'      => $lastGroup?->niveau?->libelle ?? 'N/A',
                'promotion'   => $anneeActive?->nom ?? date('Y') . '-' . (date('Y') + 1),
                'image_url'   => $photoBase64,
                'qr_data'     => $frontendUrl . '/verif/' . $etudiant->matricule,
            ];
        });

        // 3. Préparer le logo en base64
        $logoBase64 = null;
        if ($config && $config->logo) {
            $logoPath = $config->logo;
            $possibleLogoPaths = [
                storage_path('app/public/' . $logoPath),
                storage_path('app/public/configuration/' . basename($logoPath)),
                public_path('storage/' . $logoPath),
            ];

            foreach ($possibleLogoPaths as $path) {
                if (file_exists($path) && is_file($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
                    break;
                }
            }
        }

        $data = [
            'etudiants' => $cardsData,
            'logo'      => $logoBase64,
            'appName'   => $config->nom_application ?? 'ESCEN-IAI TOGO',
        ];

        // On retourne la vue qui contient le script html2pdf.js
        return view('pdfs.student-cards-modern', $data);
    }

    /**
     * Endpoint public de vérification des informations scolaires et financières d'un étudiant par son matricule.
     */
    public function verifyStudent($matricule)
    {
        $etudiant = Etudiant::where('matricule', $matricule)
            ->with(['etudiantGroups' => function ($q) {
                $q->with(['filiere', 'niveau', 'anneeScolaire'])
                  ->orderBy('id', 'desc');
            }, 'fraisEtudiant' => function ($q) {
                $q->with(['anneeScolaire', 'fraisScolarite', 'echeances'])
                  ->orderBy('id', 'desc');
            }, 'submittedDocuments'])
            ->first();

        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant non trouvé'], 404);
        }

        $lastGroup = $etudiant->etudiantGroups->first();
        $frais = $etudiant->fraisEtudiant->first();

        $photoKeys = \Illuminate\Support\Facades\Cache::remember('photo_document_keys', 3600, function () {
            return \App\Models\DocumentType::where('is_photo', true)->pluck('document_key')->toArray();
        });
        if (empty($photoKeys)) $photoKeys = ['photo', 'photo_identite'];

        $result = [
            'nom'            => $etudiant->nom,
            'prenom'         => $etudiant->prenom,
            'nom_complet'    => $etudiant->getNomCompletAttribute(),
            'matricule'      => $etudiant->matricule,
            'email'          => $etudiant->email,
            'tel'            => $etudiant->tel,
            'photo'          => $etudiant->submittedDocuments->whereIn('document_key', $photoKeys)->first()?->file_path ? asset(\Illuminate\Support\Facades\Storage::url($etudiant->submittedDocuments->whereIn('document_key', $photoKeys)->first()->file_path)) : ($etudiant->image ? asset(\Illuminate\Support\Facades\Storage::url($etudiant->image)) : null),
            'genre'          => $etudiant->genre?->value ?? null,
            'nationalite'    => $etudiant->nationalite,
            'filiere'        => $lastGroup?->filiere?->nom ?? 'N/A',
            'niveau'         => $lastGroup?->niveau?->libelle ?? 'N/A',
            'annee_scolaire' => $lastGroup?->anneeScolaire?->nom ?? 'N/A',
            'frais'          => null
        ];

        if ($frais) {
            $result['frais'] = [
                'montant_initial'      => (float)$frais->montant_initial,
                'montant_apres_bourse' => (float)$frais->montant_apres_bourse,
                'total_paye'           => (float)$frais->total_paye,
                'reste_a_payer'        => (float)$frais->reste_a_payer,
                'statut'               => $frais->statut,
                'echeances'            => collect($frais->echeances_actives)->map(function ($echeance) {
                    return [
                        'id'           => $echeance->id ?? null,
                        'libelle'      => $echeance->libelle,
                        'montant'      => (float)$echeance->montant,
                        'montant_paye' => (float)($echeance->montant_paye ?? 0),
                        'date_limite'  => isset($echeance->date_limite) ? (is_string($echeance->date_limite) ? $echeance->date_limite : $echeance->date_limite->format('Y-m-d')) : null,
                        'statut'       => $echeance->statut ?? 'en_attente'
                    ];
                })
            ];
        }

        return response()->json($result);
    }

    /**
     * Endpoint public de vérification par matricule (QR Code)
     */
    public function verifEtudiant($matricule)
    {
        $etudiant = Etudiant::where('matricule', $matricule)->with('submittedDocuments')->first();

        if (!$etudiant) {
            return response()->json([
                'success' => false,
                'message' => 'Étudiant non trouvé'
            ], 404);
        }

        $service = new \App\Services\Etudiant\ParcoursService($etudiant);
        $parcours = $service->getParcoursComplet();

        $photoUrl = null;
        $photoKeys = \Illuminate\Support\Facades\Cache::remember('photo_document_keys', 3600, function () {
            return \App\Models\DocumentType::where('is_photo', true)->pluck('document_key')->toArray();
        });
        if (empty($photoKeys)) $photoKeys = ['photo', 'photo_identite'];

        $photoDoc = $etudiant->submittedDocuments->whereIn('document_key', $photoKeys)->first();
        $photoPath = $photoDoc ? $photoDoc->file_path : $etudiant->image;

        if ($photoPath) {
            if (str_starts_with($photoPath, 'http')) {
                $photoUrl = $photoPath;
            } else {
                $photoUrl = asset(Storage::url($photoPath));
            }
        }

        $identite = $parcours['identite'];
        $identite['photo_url'] = $photoUrl;

        return response()->json([
            'success' => true,
            'data' => [
                'identite' => $identite,
                'parcours_academique' => $parcours['parcours_academique'],
                'paiements_par_annee' => $parcours['paiements_par_annee'],
                'statut_financier' => $parcours['statut_financier'],
                'bourses_obtenues' => $parcours['bourses_obtenues'],
            ]
        ]);
    }
}
