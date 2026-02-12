<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use App\Models\Evaluation;
use App\Models\Periode;
use App\Models\UniteEnseignement;
use App\Models\UniteValeur;
use App\Models\UVValidation;
use App\Models\UEValidation;
use App\Models\ReleveNote;
use App\Models\Note;
use App\Models\UVWeighting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NoteCalculationService
{

    public function calculateAndSaveForStudent(Etudiant $etudiant,AnneeScolaire $anneeScolaire,Periode $periode):ReleveNote
     {
        DB::beginTransaction();

        try {
            // 1. Récupérer le groupe de l'étudiant pour l'année en cours
            $etudiantGroup = $etudiant->etudiantGroups()
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->first();

            if (!$etudiantGroup) {
                throw new \Exception("Aucun groupe trouvé pour l'étudiant en {$anneeScolaire->libelle}");
            }

            $filiereId = $etudiantGroup->filiere_id;
            $niveauId = $etudiantGroup->niveau_id;

            // 2. Récupérer toutes les UE de la filière/niveau pour cette période
            $ues = UniteEnseignement::where('filiere_id', $filiereId)
                ->where('periode_id', $periode->id)
                ->whereHas('uniteDeValeurs', function ($q) use ($niveauId) {
                    $q->where('niveau_id', $niveauId);
                })
                ->get();

            $totalCreditsValides = 0;
            $totalCreditsNonValides = 0;
            $sommeMoyennesPonderees = 0;
            $totalCoefficients = 0;

            $ueValidations = [];

            // 3. Pour chaque UE, calculer les moyennes des UV et la moyenne de l'UE
            foreach ($ues as $ue) {
                $uvs = $ue->uniteDeValeurs()
                    ->where('niveau_id', $niveauId)
                    ->get();

                $sommeNotesUVPonderees = 0;
                $sommeCoefficientsUV = 0;
                $creditsUE = 0;
                $uvValidations = [];

                foreach ($uvs as $uv) {
                    // Récupérer les pondérations
                    $weighting = UVWeighting::where('unite_valeur_id', $uv->id)
                        ->where('filiere_id', $filiereId)
                        ->first();

                    $poidsDevoir = $weighting->poids_devoir ?? 40;
                    $poidsExamen = $weighting->poids_examen ?? 60;
                    $seuilValidation = $weighting->seuil_validation ?? 10;

                    // Calculer les moyennes de l'UV
                    $notesUV = $this->calculateUVAverage($etudiant, $uv, $anneeScolaire, $periode);

                    $moyenneUV = $notesUV['moyenne'];
                    $noteDevoir = $notesUV['devoir'];
                    $noteExamen = $notesUV['examen'];

                    $validee = $moyenneUV >= $seuilValidation;
                    $creditObtenu = $validee ? ($uv->credit ?? 0) : 0;

                    // Sauvegarder la validation UV
                    $uvValidation = UVValidation::updateOrCreate(
                        [
                            'etudiant_id' => $etudiant->id,
                            'unite_valeur_id' => $uv->id,
                            'annee_scolaire_id' => $anneeScolaire->id,
                            'periode_id' => $periode->id,
                        ],
                        [
                            'moyenne' => $moyenneUV,
                            'note_devoir' => $noteDevoir,
                            'note_examen' => $noteExamen,
                            'coefficient' => $uv->coefficient ?? 1,
                            'credit_obtenu' => $creditObtenu,
                            'validee' => $validee
                        ]
                    );

                    $uvValidations[] = $uvValidation;

                    // Accumuler pour la moyenne de l'UE
                    $coefficientUV = $uv->coefficient ?? 1;
                    $sommeNotesUVPonderees += $moyenneUV * $coefficientUV;
                    $sommeCoefficientsUV += $coefficientUV;

                    if ($validee) {
                        $creditsUE += $creditObtenu;
                    }
                }

                // Calculer la moyenne de l'UE
                $moyenneUE = $sommeCoefficientsUV > 0
                    ? round($sommeNotesUVPonderees / $sommeCoefficientsUV, 2)
                    : 0;

                $ueValidee = $moyenneUE >= 10;
                $creditUEObtenu = $ueValidee ? ($ue->credit ?? 0) : 0;

                // Déterminer le type de validation
                $typeValidation = null;
                if ($ueValidee) {
                    // Vérifier s'il y a une gratification de rattrapage
                    $gratification = $etudiant->gratifications()
                        ->where('unite_enseignement_id', $ue->id)
                        ->where('annee_scolaire_id', $anneeScolaire->id)
                        ->where('validee', true)
                        ->first();

                    $typeValidation = $gratification
                        ? 'rattrapage'
                        : ($moyenneUE >= 10 ? 'normale' : 'compensation');
                }

                // Sauvegarder la validation UE
                $ueValidation = UEValidation::updateOrCreate(
                    [
                        'etudiant_id' => $etudiant->id,
                        'unite_enseignement_id' => $ue->id,
                        'annee_scolaire_id' => $anneeScolaire->id,
                        'periode_id' => $periode->id,
                    ],
                    [
                        'moyenne' => $moyenneUE,
                        'credit_obtenu' => $creditUEObtenu,
                        'validee' => $ueValidee,
                        'type_validation' => $typeValidation
                    ]
                );

                $ueValidations[] = $ueValidation;

                // Accumuler pour la moyenne générale
                $coefficientUE = $ue->coefficient ?? 1;
                $sommeMoyennesPonderees += $moyenneUE * $coefficientUE;
                $totalCoefficients += $coefficientUE;

                if ($ueValidee) {
                    $totalCreditsValides += $creditUEObtenu;
                } else {
                    $totalCreditsNonValides += ($ue->credit ?? 0);
                }
            }

            // 4. Calculer la moyenne générale
            $moyenneGenerale = $totalCoefficients > 0
                ? round($sommeMoyennesPonderees / $totalCoefficients, 2)
                : 0;

            // 5. Sauvegarder le relevé de notes
            $releve = ReleveNote::updateOrCreate(
                [
                    'etudiant_id' => $etudiant->id,
                    'annee_scolaire_id' => $anneeScolaire->id,
                    'periode_id' => $periode->id,
                ],
                [
                    'moyenne_generale' => $moyenneGenerale,
                    'total_credits_valides' => $totalCreditsValides,
                    'total_credits_non_valides' => $totalCreditsNonValides,
                    'metadata' => [
                        'niveau_id' => $niveauId,
                        'filiere_id' => $filiereId,
                        'calcule_auto' => true
                    ]
                ]
            );

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
        $evaluations = Evaluation::where('unite_valeur_id', $uv->id)
            ->whereHas('emploiDutemp', function ($q) use ($anneeScolaire, $periode) {
                $q->where('annee_scolaire_id', $anneeScolaire->id)
                    ->where('periode_id', $periode->id);
            })
            ->get();

        $notesDevoir = collect();
        $notesExamen = collect();

        foreach ($evaluations as $evaluation) {
            $note = Note::where('etudiant_id', $etudiant->id)
                ->where('evaluation_id', $evaluation->id)
                ->first();

            if ($note) {
                if ($evaluation->type->value === 'devoir') {
                    $notesDevoir->push($note->note);
                } elseif ($evaluation->type->value === 'examen') {
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
    public function getReleveFormatted(
        Etudiant $etudiant,
        AnneeScolaire $anneeScolaire,
        Periode $periode
    ): array {
        $releve = ReleveNote::where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->where('periode_id', $periode->id)
            ->first();

        if (!$releve) {
            $releve = $this->calculateAndSaveForStudent($etudiant, $anneeScolaire, $periode);
        }

        $ueValidations = UEValidation::where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->where('periode_id', $periode->id)
            ->with('uniteEnseignement')
            ->get();

        $releveGrouped = [];

        foreach ($ueValidations as $ueValidation) {
            $ue = $ueValidation->uniteEnseignement;

            $uvValidations = UVValidation::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->where('periode_id', $periode->id)
                ->whereHas('uniteValeur', function ($q) use ($ue) {
                    $q->where('unite_enseignement_id', $ue->id);
                })
                ->with('uniteValeur')
                ->get();

            $uvs = [];
            foreach ($uvValidations as $uvValidation) {
                $uv = $uvValidation->uniteValeur;

                $uvs[] = [
                    'uv' => $uv->nom,
                    'devoir' => number_format($uvValidation->note_devoir ?? 0, 2),
                    'examen' => number_format($uvValidation->note_examen ?? 0, 2),
                    'weights_label' => "Devoir: 40% / Examen: 60%",
                    'moyenne_uv' => number_format($uvValidation->moyenne, 2),
                    'validation' => $uvValidation->validee ? 'Validé' : 'Non validé',
                    'uv_validee' => $uvValidation->validee,
                    'coefficient' => $uvValidation->coefficient
                ];
            }

            // Récupérer la gratification si existante
            $gratification = $etudiant->gratifications()
                ->where('unite_enseignement_id', $ue->id)
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->where('validee', true)
                ->first();

            $gratificationData = null;
            if ($gratification) {
                $gratificationData = [
                    'type' => 'rattrapage',
                    'motif' => $gratification->motif ?? 'Session de rattrapage réussie',
                    'date_approbation' => $gratification->date_approbation?->format('Y-m-d')
                ];
            }

            $releveGrouped[$ue->nom] = [
                'moyenne_ue' => number_format($ueValidation->moyenne, 2),
                'credit' => $ue->credit ?? 0,
                'ue_validee' => $ueValidation->validee,
                'gratification' => $gratificationData,
                'uvs' => $uvs
            ];
        }

        return [
            'user' => [
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'genre' => $etudiant->genre->value ?? 'M'
            ],
            'anne' => $anneeScolaire->libelle,
            'periode_nom' => $periode->nom,
            'releve_grouped' => $releveGrouped,
            'moyenne_generale' => number_format($releve->moyenne_generale, 2),
            'total_credits_valides' => $releve->total_credits_valides,
            'total_credits_non_valides' => $releve->total_credits_non_valides,
            'note' => 'Relevé généré automatiquement'
        ];
    }

    /**
     * Recalcule tous les relevés pour une année/période
     */
    public function recalculateAllForPeriode(AnneeScolaire $anneeScolaire, Periode $periode): void
    {
        $etudiants = Etudiant::whereHas('etudiantGroups', function ($q) use ($anneeScolaire) {
            $q->where('annee_scolaire_id', $anneeScolaire->id);
        })->get();

        foreach ($etudiants as $etudiant) {
            try {
                $this->calculateAndSaveForStudent($etudiant, $anneeScolaire, $periode);
            } catch (\Exception $e) {
                \Log::error("Erreur calcul relevé pour étudiant {$etudiant->id}: " . $e->getMessage());
            }
        }
    }
}
