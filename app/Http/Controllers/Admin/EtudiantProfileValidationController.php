<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Etudiant;
use App\Models\EtudiantProfileRequest;
use App\Notifications\Etudiants\EtudiantProfileRejectedNotification;
use App\Notifications\Etudiants\EtudiantProfileValidatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EtudiantProfileValidationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:valider-profil-etudiant');
    }

    /**
     * Liste des demandes de vérification / modification de profil
     */
    public function index(Request $request): JsonResponse
    {
        $statut = $request->get('statut', 'en_attente');
        $search = $request->get('search');

        $query = EtudiantProfileRequest::with([
            'etudiant.allEtudiantGroups.group',
            'etudiant.allEtudiantGroups.filiere',
            'etudiant.allEtudiantGroups.niveau',
            'traitePar'
        ]);

        if ($statut && $statut !== 'tous') {
            $query->where('statut', $statut);
        }

        if ($search) {
            $query->whereHas('etudiant', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $demandes */
        $demandes = $query->latest()->paginate($request->get('per_page', 20));

        // Formater les chemins de documents pour le frontend
        $demandes->through(function ($item) {
            $docsWithUrls = [];
            if (!empty($item->documents)) {
                foreach ($item->documents as $key => $path) {
                    $docsWithUrls[$key] = [
                        'key' => $key,
                        'path' => $path,
                        'url' => asset(Storage::url($path)),
                    ];
                }
            }
            $item->documents_formatted = $docsWithUrls;
            return $item;
        });

        $counts = [
            'en_attente' => EtudiantProfileRequest::where('statut', 'en_attente')->count(),
            'valide' => EtudiantProfileRequest::where('statut', 'valide')->count(),
            'rejete' => EtudiantProfileRequest::where('statut', 'rejete')->count(),
        ];

        return response()->json([
            'demandes' => $demandes,
            'counts' => $counts,
        ]);
    }

    /**
     * Détails d'une demande avec comparaison des données actuelles vs demandées
     */
    public function show(EtudiantProfileRequest $demande): JsonResponse
    {
        $demande->load([
            'etudiant.tuteur',
            'etudiant.responsable',
            'etudiant.submittedDocuments',
            'etudiant.allEtudiantGroups.group',
            'etudiant.allEtudiantGroups.filiere',
            'etudiant.allEtudiantGroups.niveau',
            'traitePar'
        ]);

        $docsWithUrls = [];
        if (!empty($demande->documents)) {
            foreach ($demande->documents as $key => $path) {
                $type = DocumentType::where('document_key', $key)->first();
                $docsWithUrls[$key] = [
                    'key' => $key,
                    'nom_affichage' => $type?->nom_affichage ?? ucfirst(str_replace('_', ' ', $key)),
                    'path' => $path,
                    'url' => asset(Storage::url($path)),
                ];
            }
        }

        return response()->json([
            'demande' => $demande,
            'documents_details' => $docsWithUrls,
            'profil_actuel' => [
                'nom' => $demande->etudiant->nom,
                'prenom' => $demande->etudiant->prenom,
                'nom_jeune_fille' => $demande->etudiant->nom_jeune_fille,
                'genre' => $demande->etudiant->genre?->value ?? $demande->etudiant->genre,
                'date_naissance' => $demande->etudiant->date_naissance?->format('Y-m-d'),
                'lieu_naissance' => $demande->etudiant->lieu_naissance,
                'nationalite' => $demande->etudiant->nationalite,
                'tel' => $demande->etudiant->tel,
                'email' => $demande->etudiant->email,
                'matricule' => $demande->etudiant->matricule,
                'biographie' => $demande->etudiant->biographie,
                'photo_url' => $demande->etudiant->image ? asset(Storage::url($demande->etudiant->image)) : null,
                'tuteur' => $demande->etudiant->tuteur,
                'responsable' => $demande->etudiant->responsable,
            ]
        ]);
    }

    /**
     * Valider la demande et appliquer définitivement les données
     */
    public function valider(EtudiantProfileRequest $demande): JsonResponse
    {
        $etudiant = $demande->etudiant;
        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant introuvable.'], 404);
        }

        return DB::transaction(function () use ($demande, $etudiant) {
            $data = $demande->data ?? [];

            // 1. Mettre à jour les informations personnelles de l'étudiant
            $etudiantUpdate = [
                'nom' => $data['nom'] ?? $etudiant->nom,
                'prenom' => $data['prenom'] ?? $etudiant->prenom,
                'nom_jeune_fille' => $data['nom_jeune_fille'] ?? $etudiant->nom_jeune_fille,
                'genre' => $data['genre'] ?? $etudiant->genre,
                'date_naissance' => $data['date_naissance'] ?? $etudiant->date_naissance,
                'lieu_naissance' => $data['lieu_naissance'] ?? $etudiant->lieu_naissance,
                'nationalite' => $data['nationalite'] ?? $etudiant->nationalite,
                'tel' => $data['tel'] ?? $etudiant->tel,
                'biographie' => $data['biographie'] ?? $etudiant->biographie,
                'profil_verifie_at' => now(),
            ];
            $etudiant->update($etudiantUpdate);

            // 2. Mettre à jour le tuteur
            if (!empty($data['tuteur'])) {
                $etudiant->tuteur()->updateOrCreate(
                    ['owner_id' => $etudiant->id, 'owner_type' => Etudiant::class],
                    [
                        'nom' => $data['tuteur']['nom'] ?? '',
                        'prenom' => $data['tuteur']['prenom'] ?? '',
                        'tel' => $data['tuteur']['tel'] ?? '',
                        'email' => $data['tuteur']['email'] ?? null,
                        'profession' => $data['tuteur']['profession'] ?? null,
                        'adresse' => $data['tuteur']['adresse'] ?? null,
                    ]
                );
            }

            // 3. Mettre à jour le responsable financier
            if (!empty($data['responsable'])) {
                $etudiant->responsable()->updateOrCreate(
                    ['owner_id' => $etudiant->id, 'owner_type' => Etudiant::class],
                    [
                        'nom' => $data['responsable']['nom'] ?? '',
                        'prenom' => $data['responsable']['prenom'] ?? '',
                        'tel' => $data['responsable']['tel'] ?? '',
                        'email' => $data['responsable']['email'] ?? null,
                        'adresse' => $data['responsable']['adresse'] ?? null,
                    ]
                );
            }

            // 4. Copier les documents vers le stockage définitif et mettre à jour submittedDocuments
            if (!empty($demande->documents)) {
                foreach ($demande->documents as $docKey => $requestPath) {
                    if (Storage::disk('public')->exists($requestPath)) {
                        $permanentPath = 'etudiants/documents/' . basename($requestPath);
                        Storage::disk('public')->copy($requestPath, $permanentPath);

                        $isPhoto = DocumentType::where('document_key', $docKey)->where('is_photo', true)->exists();
                        if ($isPhoto || in_array($docKey, ['photo', 'photo_identite'])) {
                            $etudiant->update(['image' => $permanentPath]);
                        }

                        $etudiant->submittedDocuments()->updateOrCreate(
                            ['document_key' => $docKey],
                            [
                                'file_path' => $permanentPath,
                                'statut' => 'valide',
                            ]
                        );
                    }
                }
            }

            // 5. Clôturer la demande
            $demande->update([
                'statut' => 'valide',
                'motif_refus' => null,
                'traite_par' => auth()->id(),
                'traite_le' => now(),
            ]);

            // 6. Notifier l'étudiant par email
            try {
                $etudiant->notify(new EtudiantProfileValidatedNotification($etudiant));
            } catch (\Throwable $e) {
                Log::error('Erreur notification validation etudiant: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'La demande de profil a été validée avec succès et appliquée sur le dossier officiel.',
                'demande' => $demande->fresh(),
            ]);
        });
    }

    /**
     * Rejeter la demande avec motif obligatoire
     */
    public function rejeter(Request $request, EtudiantProfileRequest $demande): JsonResponse
    {
        $request->validate([
            'motif_refus' => 'required|string|min:3|max:1000',
        ], [
            'motif_refus.required' => 'Le motif du refus est obligatoire.',
            'motif_refus.min' => 'Le motif du refus doit comporter au moins 3 caractères.',
        ]);

        $etudiant = $demande->etudiant;
        if (!$etudiant) {
            return response()->json(['message' => 'Étudiant introuvable.'], 404);
        }

        $demande->update([
            'statut' => 'rejete',
            'motif_refus' => $request->motif_refus,
            'traite_par' => auth()->id(),
            'traite_le' => now(),
        ]);

        // Notifier l'étudiant par mail avec le motif
        try {
            $etudiant->notify(new EtudiantProfileRejectedNotification($etudiant, $request->motif_refus));
        } catch (\Throwable $e) {
            Log::error('Erreur notification rejet etudiant: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'La demande a été rejetée. L\'étudiant a été informé du motif par email.',
            'demande' => $demande->fresh(),
        ]);
    }
}
