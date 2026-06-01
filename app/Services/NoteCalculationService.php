<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use App\Models\Evaluation;
use App\Models\Periode;
use App\Models\UniteEnseignement;
use App\Models\UniteValeur;
use App\Models\UvValidation;
use App\Models\UeValidation;
use App\Models\ReleveNote;
use App\Models\Note;
use App\Models\UVWeighting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NoteCalculationService
{
    public function calculateAndSaveForStudent(Etudiant $etudiant, AnneeScolaire $anneeScolaire, Periode $periode): ReleveNote
    {
        DB::beginTransaction();

        try {
            // 1. Identifier le groupe et le cursus (sans filtre caché)
            $etudiantGroup = $etudiant->etudiantGroups()
                ->withoutGlobalScopes()
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->first();

            if (!$etudiantGroup) {
                throw new \Exception("L'étudiant n'est inscrit dans aucun groupe pour l'année {$anneeScolaire->nom}");
            }

            $filiereId = $etudiantGroup->filiere_id;
            $niveauId = $etudiantGroup->niveau_id;

            // 2. Créer ou réinitialiser le relevé
            $releve = ReleveNote::updateOrCreate(
                [
                    'etudiant_id' => $etudiant->id,
                    'annee_scolaire_id' => $anneeScolaire->id,
                    'periode_id' => $periode->id,
                ],
                [
                    'moyenne_generale' => 0,
                    'total_credits_valides' => 0,
                    'total_credits_non_valides' => 0,
                    'metadata' => [
                        'niveau_id' => $niveauId,
                        'filiere_id' => $filiereId,
                        'recalculated_at' => now()->toDateTimeString()
                    ]
                ]
            );

            // 3. Récupérer les matières (UV) directement
            $uvs = UniteValeur::withoutGlobalScopes()
                ->where('filiere_id', $filiereId)
                ->where(function($q) use ($periode) {
                    $q->where('periode_id', $periode->id)->orWhereNull('periode_id');
                })
                ->get();

            // Fallback si vide
            if ($uvs->isEmpty()) {
                $uvs = UniteValeur::withoutGlobalScopes()
                    ->where('filiere_id', $filiereId)
                    ->get();
            }

            $totalCreditsValides = 0;
            $totalCreditsNonValides = 0;
            $sommeMoyennesPonderees = 0;
            $totalCoefficients = 0;

            foreach ($uvs as $uv) {
                // Calcul de la moyenne de l'UV
                $notesCalculated = $this->calculateUVAverage($etudiant, $uv, $anneeScolaire, $periode);
                
                $moyenneUV = $notesCalculated['moyenne'] ?? 0;
                $coefUV = $uv->coefficient ?? 1;
                $validee = $moyenneUV >= 10;
                $creditUV = $validee ? ($uv->credit ?? 0) : 0;

                // Enregistrement UV Validation
                UvValidation::updateOrCreate(
                    [
                        'unite_valeur_id' => $uv->id,
                        'etudiant_id' => $etudiant->id,
                        'annee_scolaire_id' => $anneeScolaire->id,
                        'periode_id' => $periode->id,
                    ],
                    [
                        'releve_note_id' => $releve->id,
                        'moyenne' => $moyenneUV,
                        'note_devoir' => $notesCalculated['devoir'] ?? 0,
                        'note_examen' => $notesCalculated['examen'] ?? 0,
                        'coefficient' => $coefUV,
                        'credit_obtenu' => $creditUV,
                        'validee' => $validee
                    ]
                );

                // Pour que l'affichage UE fonctionne toujours (car requis par le frontend), 
                // on crée/maintient une UE validation "fictive" ou on lie à l'UE parente si elle existe
                if ($uv->unite_enseignement_id) {
                    UeValidation::updateOrCreate(
                        [
                            'unite_enseignement_id' => $uv->unite_enseignement_id,
                            'etudiant_id' => $etudiant->id,
                            'annee_scolaire_id' => $anneeScolaire->id,
                            'periode_id' => $periode->id,
                        ],
                        [
                            'releve_note_id' => $releve->id,
                            'moyenne' => $moyenneUV, 
                            'credit_obtenu' => $validee ? ($uv->ue?->credit ?? 0) : 0, 
                            'validee' => $validee
                        ]
                    );
                }

                $sommeMoyennesPonderees += ($moyenneUV * $coefUV);
                $totalCoefficients += $coefUV;

                if ($validee) {
                    $totalCreditsValides += $coefUV;
                } else {
                    $totalCreditsNonValides += $coefUV;
                }
            }

            // 5. Finalisation du relevé
            $moyenneGenerale = $totalCoefficients > 0 ? round($sommeMoyennesPonderees / $totalCoefficients, 2) : 0;

            $releve->update([
                'moyenne_generale' => $moyenneGenerale,
                'total_credits_valides' => $totalCreditsValides,
                'total_credits_non_valides' => $totalCreditsNonValides
            ]);

            DB::commit();
            return $releve;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calcule la moyenne d'une UV avec ses pondérations
     */
    private function calculateUVAverage(
        Etudiant $etudiant,
        UniteValeur $uv,
        AnneeScolaire $anneeScolaire,
        Periode $periode
    ): array {
        // Récupérer les évaluations de l'UV pour cette période
        // On considère que l'ID de la période correspond souvent au semestre (1 ou 2)
        $evaluations = Evaluation::withoutGlobalScopes()
            ->where('unite_valeur_id', $uv->id)
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->where(function($q) use ($periode) {
                 // On tente de filtrer par semestre si la colonne existe
                 $q->where('semestre', $periode->id)
                   ->orWhereNull('semestre');
            })
            ->get();

        $notesDevoir = collect();
        $notesExamen = collect();

        foreach ($evaluations as $evaluation) {
            $note = Note::withoutGlobalScopes()
                ->where('etudiant_id', $etudiant->id)
                ->where('evaluation_id', $evaluation->id)
                ->first();

            if ($note) {
                $type = strtolower($evaluation->type->value);
                if (in_array($type, ['devoir', 'interrogation', 'tp', 'exposé'])) {
                    $notesDevoir->push($note->note);
                } elseif ($type === 'examen') {
                    $notesExamen->push($note->note);
                }
            }
        }

        // Récupérer les pondérations
        $weighting = UVWeighting::where('unite_valeur_id', $uv->id)->first();
        $poidsDevoir = $weighting->poids_devoir ?? 40;
        $poidsExamen = $weighting->poids_examen ?? 60;

        // Calculer les moyennes
        $moyenneDevoir = $notesDevoir->isNotEmpty() ? $notesDevoir->average() : null;
        $moyenneExamen = $notesExamen->isNotEmpty() ? $notesExamen->average() : null;

        $moyenne = 0;
        $totalPoids = 0;

        if ($moyenneDevoir !== null) {
            $moyenne += $moyenneDevoir * ($poidsDevoir / 100);
            $totalPoids += $poidsDevoir;
        }

        if ($moyenneExamen !== null) {
            $moyenne += $moyenneExamen * ($poidsExamen / 100);
            $totalPoids += $poidsExamen;
        }

        if ($totalPoids > 0) {
            $moyenne = round($moyenne * (100 / $totalPoids), 2);
        }

        return [
            'moyenne' => $moyenne,
            'devoir' => $moyenneDevoir,
            'examen' => $moyenneExamen
        ];
    }

    /**
     * Récupère le relevé de notes formaté comme dans le JSON
     */
    public function getReleveFormatted(Etudiant $etudiant, AnneeScolaire $anneeScolaire, Periode $periode): array
    {

        $releve = ReleveNote::with([
            'ueValidations.uniteEnseignement',
            'uvValidations.uniteValeur'
        ])
            ->where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->where('periode_id', $periode->id)
            ->first();

        if (!$releve) {
            $releve = $this->calculateAndSaveForStudent($etudiant, $anneeScolaire, $periode);
            $releve->load([
                'ueValidations.uniteEnseignement',
                'uvValidations.uniteValeur'
            ]);
        }

        $releveGrouped = [];

        if ($releve->ueValidations->isEmpty()) {
            abort(500, "DIAGNOSTIC : Le relevé ID {$releve->id} n'a aucune UE validée (ue_validations vide).");
        }

        foreach ($releve->ueValidations as $ueValidation) {

            $ue = $ueValidation->uniteEnseignement;

            // récupérer les UV de cette UE à partir du même relevé
            $uvValidations = $releve->uvValidations->filter(function($uvv) use ($ue) {
                return $uvv->uniteValeur && $uvv->uniteValeur->unite_enseignement_id == $ue->id;
            });

            $uvs = [];

            foreach ($uvValidations as $uvValidation) {

                $uv = $uvValidation->uniteValeur;
                if (!$uv) continue;

                $uvs[] = [
                    'nom' => $uv->nom,
                    'devoir' => number_format($uvValidation->note_devoir ?? 0, 2),
                    'examen' => number_format($uvValidation->note_examen ?? 0, 2),
                    'moyenne_uv' => number_format($uvValidation->moyenne, 2),
                    'note_ponderee' => number_format($uvValidation->moyenne * $uvValidation->coefficient, 2),
                    'validation' => $uvValidation->validee ? 'Validé' : 'Non validé',
                    'coefficient' => $uvValidation->coefficient
                ];
            }

            $releveGrouped[] = [
                'ue' => $ue->nom,
                'moyenne_ue' => number_format($ueValidation->moyenne, 2),
                'credit' => $ue->credit ?? 0,
                'ue_validee' => $ueValidation->validee,
                'type_validation' => $ueValidation->type_validation,
                'uvs' => $uvs
            ];
        }

        return [
            'etudiant' => [
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'matricule' => $etudiant->matricule,
                'genre' => $etudiant->genre->value ?? 'M',
                'dernier_groupe' => ($dg = $etudiant->etudiantGroups()->latest('id')->first()) ? [
                    'group' => $dg->group ? ['nom' => $dg->group->nom] : null,
                    'filiere' => $dg->filiere ? ['nom' => $dg->filiere->nom] : null,
                    'niveau' => $dg->niveau ? ['nom' => $dg->niveau->libelle] : null,
                ] : null
            ],
            'annee_scolaire' => $anneeScolaire->nom,
            'periode' => $periode->nom,
            'date_generation' => $releve->created_at->format('Y-m-d'),
            'moyenne_generale' => number_format($releve->moyenne_generale, 2),
            'total_credits_valides' => $releve->total_credits_valides,
            'total_credits_non_valides' => $releve->total_credits_non_valides,
            'total_coefficients' => $releve->uvValidations->sum('coefficient'),
            'total_notes_ponderees' => number_format($releve->uvValidations->sum(fn($uvv) => $uvv->moyenne * $uvv->coefficient), 2),
            'logo_url' => \App\Models\Configuration::where('key', 'logo_etablissement')->first()?->value 
                ? asset('storage/' . \App\Models\Configuration::where('key', 'logo_etablissement')->first()->value)
                : null,
            'ues' => $releveGrouped
        ];
    }



    // Dans votre NoteService

    /**
     * Récupère tous les relevés d'un étudiant, groupés par année scolaire et période
     */
    public function getAllRelevesForStudent(Etudiant $etudiant): array
    {
        // Récupérer tous les relevés de l'étudiant
        $releves = ReleveNote::with([
            'ueValidations.uniteEnseignement',
            'uvValidations.uniteValeur',
            'anneeScolaire',
            'periode'
        ])
            ->where('etudiant_id', $etudiant->id)
            ->orderBy('annee_scolaire_id', 'desc')
            ->orderBy('periode_id', 'desc')
            ->get();

        $relevesFormatted = [];

        foreach ($releves as $releve) {
            $releveGrouped = [];

            foreach ($releve->ueValidations as $ueValidation) {
                $ue = $ueValidation->uniteEnseignement;

                // Récupérer les UV de cette UE
                $uvValidations = $releve->uvValidations
                    ->where('uniteValeur.unite_enseignement_id', $ue->id);

                $uvs = [];

                foreach ($uvValidations as $uvValidation) {
                    $uv = $uvValidation->uniteValeur;

                    $uvs[] = [
                        'nom' => $uv->nom,
                        'devoir' => number_format($uvValidation->note_devoir ?? 0, 2),
                        'examen' => number_format($uvValidation->note_examen ?? 0, 2),
                        'moyenne_uv' => number_format($uvValidation->moyenne, 2),
                        'note_ponderee' => number_format($uvValidation->moyenne * $uvValidation->coefficient, 2),
                        'validation' => $uvValidation->validee ? 'Validé' : 'Non validé',
                        'coefficient' => $uvValidation->coefficient
                    ];
                }

                $releveGrouped[] = [
                    'ue' => $ue->nom,
                    'moyenne_ue' => number_format($ueValidation->moyenne, 2),
                    'credit' => $ue->credit ?? 0,
                    'ue_validee' => $ueValidation->validee,
                    'type_validation' => $ueValidation->type_validation,
                    'uvs' => $uvs
                ];
            }

            $relevesFormatted[] = [
                'id' => $releve->id,
                'etudiant' => [
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'genre' => $etudiant->genre->value ?? 'M'
                ],
                'annee_scolaire' => $releve->anneeScolaire->nom,
                'periode' => $releve->periode->nom,
                'periode_id' => $releve->periode_id,
                'date_generation' => $releve->created_at->format('Y-m-d'),
                'moyenne_generale' => number_format($releve->moyenne_generale, 2),
                'total_credits_valides' => $releve->total_credits_valides,
                'total_credits_non_valides' => $releve->total_credits_non_valides,
                'total_coefficients' => $releve->uvValidations->sum('coefficient'),
                'total_notes_ponderees' => number_format($releve->uvValidations->sum(fn($uvv) => $uvv->moyenne * $uvv->coefficient), 2),
                'logo_url' => \App\Models\Configuration::where('key', 'logo_etablissement')->first()?->value 
                    ? asset('storage/' . \App\Models\Configuration::where('key', 'logo_etablissement')->first()->value)
                    : null,
                'ues' => $releveGrouped
            ];
        }

        return $relevesFormatted;
    }

    /**
     * Récupère les relevés d'un étudiant avec possibilité de filtrer par année
     */
    public function getRelevesByYear(Etudiant $etudiant, ?int $anneeScolaireId = null): array
    {
        $query = ReleveNote::with([
            'ueValidations.uniteEnseignement',
            'uvValidations.uniteValeur',
            'anneeScolaire',
            'periode'
        ])
            ->where('etudiant_id', $etudiant->id);

        if ($anneeScolaireId) {
            $query->where('annee_scolaire_id', $anneeScolaireId);
        }

        $releves = $query->orderBy('annee_scolaire_id', 'desc')
            ->orderBy('periode_id', 'desc')
            ->get();

        $relevesFormatted = [];

        foreach ($releves as $releve) {
            $releveGrouped = [];

            foreach ($releve->ueValidations as $ueValidation) {
                $ue = $ueValidation->uniteEnseignement;

                $uvValidations = $releve->uvValidations->filter(function($uvv) use ($ue) {
                    return $uvv->uniteValeur && $uvv->uniteValeur->unite_enseignement_id == $ue->id;
                });

                $uvs = [];

                foreach ($uvValidations as $uvValidation) {
                    $uv = $uvValidation->uniteValeur;

                    $uvs[] = [
                        'nom' => $uv->nom,
                        'devoir' => number_format($uvValidation->note_devoir ?? 0, 2),
                        'examen' => number_format($uvValidation->note_examen ?? 0, 2),
                        'moyenne_uv' => number_format($uvValidation->moyenne, 2),
                        'note_ponderee' => number_format($uvValidation->moyenne * $uvValidation->coefficient, 2),
                        'validation' => $uvValidation->validee ? 'Validé' : 'Non validé',
                        'coefficient' => $uvValidation->coefficient
                    ];
                }

                $releveGrouped[] = [
                    'ue' => $ue->nom,
                    'moyenne_ue' => number_format($ueValidation->moyenne, 2),
                    'credit' => $ue->credit ?? 0,
                    'ue_validee' => $ueValidation->validee,
                    'type_validation' => $ueValidation->type_validation,
                    'uvs' => $uvs
                ];
            }

            // Charger les infos du groupe si non présentes
            $dernierGroup = $etudiant->etudiantGroups()->latest('id')->first();
            
            $relevesFormatted[] = [
                'id' => $releve->id,
                'etudiant' => [
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'genre' => $etudiant->genre->value ?? 'M',
                    'dernier_groupe' => $dernierGroup ? [
                        'group' => $dernierGroup->group ? ['nom' => $dernierGroup->group->nom] : null,
                        'filiere' => $dernierGroup->filiere ? ['nom' => $dernierGroup->filiere->nom] : null,
                        'niveau' => $dernierGroup->niveau ? ['nom' => $dernierGroup->niveau->libelle] : null,
                    ] : null
                ],
                'annee_scolaire' => $releve->anneeScolaire->nom,
                'annee_scolaire_id' => $releve->annee_scolaire_id,
                'periode' => $releve->periode->nom,
                'periode_id' => $releve->periode_id,
                'date_generation' => $releve->created_at->format('Y-m-d H:i:s'),
                'moyenne_generale' => number_format($releve->moyenne_generale, 2),
                'total_credits_valides' => $releve->total_credits_valides,
                'total_credits_non_valides' => $releve->total_credits_non_valides,
                'total_coefficients' => $releve->uvValidations->sum('coefficient'),
                'total_notes_ponderees' => number_format($releve->uvValidations->sum(fn($uvv) => $uvv->moyenne * $uvv->coefficient), 2),
                'ues_count' => count($releveGrouped),
                'ues' => $releveGrouped
            ];
        }

        return $relevesFormatted;
    }
}
