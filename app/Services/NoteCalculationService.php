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

            if ($etudiantGroup) {
                $filiereId = $etudiantGroup->filiere_id;
                $niveauId = $etudiantGroup->niveau_id;
            } else {
                // Option 1: Tolérance pour les étudiants transférés ou en rattrapage
                // Vérifier si l'étudiant a des notes pour cette période
                $hasNotes = Note::withoutGlobalScopes()->where('notes.etudiant_id', $etudiant->id)
                    ->join('unite_valeurs', 'notes.unite_valeur_id', '=', 'unite_valeurs.id')
                    ->where('unite_valeurs.periode_id', $periode->id)
                    ->exists();

                if (!$hasNotes) {
                    throw new \Exception("L'étudiant n'est inscrit dans aucun groupe pour l'année {$anneeScolaire->nom} et n'a aucune note pour cette période.");
                }

                // Déduire la filière depuis le dernier groupe de l'étudiant
                $latestGroup = $etudiant->etudiantGroups()->withoutGlobalScopes()->latest('id')->first();
                if (!$latestGroup) {
                    throw new \Exception("Impossible de déterminer la filière de l'étudiant (aucun historique de groupe).");
                }

                $filiereId = $latestGroup->filiere_id;
                $niveauId = $latestGroup->niveau_id;
            }

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

            // 3. Récupérer les matières (UV) directement, uniquement celles évaluées
            $uvQuery = UniteValeur::withoutGlobalScopes()
                ->where('filiere_id', $filiereId)
                ->whereHas('evaluations', function ($q) use ($anneeScolaire, $periode) {
                    $q->where('annee_scolaire_id', $anneeScolaire->id)
                      ->where(function ($q2) use ($periode) {
                          $q2->where('semestre', $periode->id)->orWhereNull('semestre');
                      });
                });

            // Exclusion des matières qui n'ont aucune période assignée
            $uvs = (clone $uvQuery)
                ->where('periode_id', $periode->id)
                ->get();

            // Fallback si vide, mais on garde la restriction sur les évaluations et la période
            if ($uvs->isEmpty()) {
                $uvs = $uvQuery->where('periode_id', $periode->id)->get();
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
                        'note_devoir' => $notesCalculated['devoir'] ?? null,
                        'note_examen' => $notesCalculated['examen'] ?? null,
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

        // Récupérer la configuration globale pour les examens uniquement
        $examensUniquement = \App\Models\Configuration::where('key', 'examens_uniquement')->value('value') == 1;

        if ($examensUniquement) {
            $poidsDevoir = 0;
            $poidsExamen = 100;
        }

        if ($moyenneDevoir !== null && !$examensUniquement) {
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

    public function formatReleveModel(ReleveNote $releve): array
    {
        $releveGrouped = [];
        $assignedUvIds = [];

        // Option B: VÃ©rifier s'il y a des devoirs dans ce relevÃ©
        $globalExamensUniquement = \App\Models\Configuration::where('key', 'examens_uniquement')->value('value') == 1;
        $hasDevoirs = $releve->uvValidations->contains(function($uvv) {
            return !is_null($uvv->note_devoir);
        });
        $examensUniquement = $globalExamensUniquement && !$hasDevoirs;

        foreach ($releve->ueValidations as $ueValidation) {
            $ue = $ueValidation->uniteEnseignement;
            if (!$ue) continue;

            // récupérer les UV de cette UE à partir du même relevé
            $uvValidations = $releve->uvValidations->filter(function($uvv) use ($ue) {
                return $uvv->uniteValeur && $uvv->uniteValeur->unite_enseignement_id == $ue->id;
            });

            $uvs = [];

            foreach ($uvValidations as $uvValidation) {
                $uv = $uvValidation->uniteValeur;
                if (!$uv) continue;
                
                $assignedUvIds[] = $uvValidation->id;

                $weighting = \App\Models\UVWeighting::where('unite_valeur_id', $uv->id)->first();

                $uvs[] = [
                    'nom' => $uv->nom,
                    'code' => $uv->code,
                    'devoir' => ($examensUniquement || is_null($uvValidation->note_devoir)) ? null : number_format($uvValidation->note_devoir, 2),
                    'examen' => is_null($uvValidation->note_examen) ? null : number_format($uvValidation->note_examen, 2),
                    'moyenne_uv' => number_format($uvValidation->moyenne, 2),
                    'note_ponderee' => number_format($uvValidation->moyenne * $uvValidation->coefficient, 2),
                    'validation' => $uvValidation->validee ? 'Validé' : 'Non validé',
                    'coefficient' => $uvValidation->coefficient,
                    'poids_devoir' => $examensUniquement ? 0 : ($weighting->poids_devoir ?? 40),
                    'poids_examen' => $examensUniquement ? 100 : ($weighting->poids_examen ?? 60),
                    'examens_uniquement' => $examensUniquement
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

        // Handle UVs without UE
        $unassignedUvs = $releve->uvValidations->filter(function($uvv) use ($assignedUvIds) {
            return !in_array($uvv->id, $assignedUvIds);
        });

        if ($unassignedUvs->isNotEmpty()) {
            $uvs = [];
            $totalMoyenne = 0;
            $totalCoef = 0;
            $totalCredits = 0;
            
            foreach ($unassignedUvs as $uvValidation) {
                $uv = $uvValidation->uniteValeur;
                if (!$uv) continue;
                
                $totalMoyenne += $uvValidation->moyenne * $uvValidation->coefficient;
                $totalCoef += $uvValidation->coefficient;
                $totalCredits += $uvValidation->credit_obtenu;
                
                $weighting = \App\Models\UVWeighting::where('unite_valeur_id', $uv->id)->first();

                $uvs[] = [
                    'nom' => $uv->nom,
                    'code' => $uv->code,
                    'devoir' => ($examensUniquement || is_null($uvValidation->note_devoir)) ? null : number_format($uvValidation->note_devoir, 2),
                    'examen' => is_null($uvValidation->note_examen) ? null : number_format($uvValidation->note_examen, 2),
                    'moyenne_uv' => number_format($uvValidation->moyenne, 2),
                    'note_ponderee' => number_format($uvValidation->moyenne * $uvValidation->coefficient, 2),
                    'validation' => $uvValidation->validee ? 'Validé' : 'Non validé',
                    'coefficient' => $uvValidation->coefficient,
                    'poids_devoir' => $examensUniquement ? 0 : ($weighting->poids_devoir ?? 40),
                    'poids_examen' => $examensUniquement ? 100 : ($weighting->poids_examen ?? 60),
                    'examens_uniquement' => $examensUniquement
                ];
            }
            
            if (count($uvs) > 0) {
                $moyenne_ue = $totalCoef > 0 ? $totalMoyenne / $totalCoef : 0;
                $releveGrouped[] = [
                    'ue' => 'MATIÈRES GÉNÉRALES',
                    'moyenne_ue' => number_format($moyenne_ue, 2),
                    'credit' => $totalCredits,
                    'ue_validee' => $moyenne_ue >= 10,
                    'type_validation' => null,
                    'uvs' => $uvs
                ];
            }
        }

        return [
            'id' => $releve->id,
            'etudiant' => $releve->etudiant ? [
                'nom' => $releve->etudiant->nom,
                'prenom' => $releve->etudiant->prenom,
                'slug' => $releve->etudiant->slug,
                'matricule' => $releve->etudiant->matricule,
                'genre' => $releve->etudiant->genre->value ?? 'M',
                'dernier_groupe' => ($dg = $releve->etudiant->etudiantGroups()->latest('id')->first()) ? [
                    'group' => $dg->group ? ['nom' => $dg->group->nom] : null,
                    'filiere' => $dg->filiere ? ['nom' => $dg->filiere->nom] : null,
                    'niveau' => $dg->niveau ? ['nom' => $dg->niveau->libelle] : null,
                ] : null
            ] : null,
            'annee_scolaire' => $releve->anneeScolaire?->nom,
            'periode' => $releve->periode?->nom,
            'date_generation' => $releve->created_at->format('Y-m-d'),
            'moyenne_generale' => number_format((float)($releve->moyenne_generale ?? 0), 2),
            'total_credits_valides' => $releve->total_credits_valides,
            'total_credits_non_valides' => $releve->total_credits_non_valides,
            'total_coefficients' => $releve->uvValidations->sum('coefficient'),
            'total_notes_ponderees' => number_format((float)($releve->uvValidations->sum(fn($uvv) => $uvv->moyenne * $uvv->coefficient)), 2),
            'created_at' => $releve->created_at,
            'logo_url' => \App\Models\Configuration::where('key', 'logo_etablissement')->first()?->value 
                ? asset('storage/' . \App\Models\Configuration::where('key', 'logo_etablissement')->first()->value)
                : null,
            'configurations' => \App\Models\Configuration::pluck('value', 'key')->toArray(),
            'ues' => $releveGrouped
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

        if ($releve->ueValidations->isEmpty()) {
            // On peut logger l'info si besoin, mais on ne bloque plus le processus.
            \Illuminate\Support\Facades\Log::info("Le relevé ID {$releve->id} n'a aucune UE validée (ue_validations vide).");
        }

        return $this->formatReleveModel($releve);
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
            $relevesFormatted[] = $this->formatReleveModel($releve);
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
            $relevesFormatted[] = $this->formatReleveModel($releve);
        }

        return $relevesFormatted;
    }
}
