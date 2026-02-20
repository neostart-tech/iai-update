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

            //Récupérer le groupe de l'étudiant
            $etudiantGroup = $etudiant->etudiantGroups()
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->first();

            if (!$etudiantGroup) {
                throw new \Exception("Aucun groupe trouvé pour l'étudiant en {$anneeScolaire->libelle}");
            }

            $filiereId = $etudiantGroup->filiere_id;
            $niveauId = $etudiantGroup->niveau_id;

            //Créer ou récupérer le relevé AVANT les calculs
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
                    ]
                ]
            );

            // Récupérer les UE
            $ues = UniteEnseignement::where('filiere_id', $filiereId)
                ->where('periode_id', $periode->id)
                ->whereHas('uniteDeValeurs', function ($q) use ($filiereId) {
                    $q->where('filiere_id', $filiereId);
                })
                ->get();

            $totalCreditsValides = 0;
            $totalCreditsNonValides = 0;
            $sommeMoyennesPonderees = 0;
            $totalCoefficients = 0;

            foreach ($ues as $ue) {

                $uvs = $ue->uniteDeValeurs()->get();

                $sommeNotesUVPonderees = 0;
                $sommeCoefficientsUV = 0;
                $creditsUE = 0;

                foreach ($uvs as $uv) {

                    $weighting = UVWeighting::where('unite_valeur_id', $uv->id)
                        ->where('filiere_id', $filiereId)
                        ->first();

                    $poidsDevoir = $weighting->poids_devoir ?? 40;
                    $poidsExamen = $weighting->poids_examen ?? 60;
                    $seuilValidation = $weighting->seuil_validation ?? 10;

                    $notesUV = $this->calculateUVAverage(
                        $etudiant,
                        $uv,
                        $anneeScolaire,
                        $periode
                    );

                    $moyenneUV = $notesUV['moyenne'] ?? 0;
                    $noteDevoir = $notesUV['devoir'] ?? 0;
                    $noteExamen = $notesUV['examen'] ?? 0;

                    $validee = $moyenneUV >= $seuilValidation;
                    $creditObtenu = $validee ? ($uv->credit ?? 0) : 0;

                    //Sauvegarde UV avec releve_note_id
                    UvValidation::updateOrCreate(
                        [
                            'etudiant_id' => $etudiant->id,
                            'unite_valeur_id' => $uv->id,
                            'annee_scolaire_id' => $anneeScolaire->id,
                            'periode_id' => $periode->id,
                        ],
                        [
                            'releve_note_id' => $releve->id,
                            'moyenne' => $moyenneUV,
                            'note_devoir' => $noteDevoir,
                            'note_examen' => $noteExamen,
                            'coefficient' => $uv->coefficient ?? 1,
                            'credit_obtenu' => $creditObtenu,
                            'validee' => $validee
                        ]
                    );

                    $coefficientUV = $uv->coefficient ?? 1;

                    $sommeNotesUVPonderees += $moyenneUV * $coefficientUV;
                    $sommeCoefficientsUV += $coefficientUV;

                    if ($validee) {
                        $creditsUE += $creditObtenu;
                    }
                }

                // Calcul moyenne UE
                $moyenneUE = $sommeCoefficientsUV > 0
                    ? round($sommeNotesUVPonderees / $sommeCoefficientsUV, 2)
                    : 0;

                $ueValidee = $moyenneUE >= 10;
                $creditUEObtenu = $ueValidee ? ($ue->credit ?? 0) : 0;

                $typeValidation = null;

                if ($ueValidee) {
                    $gratification = $etudiant->gratifications()
                        ->where('unite_enseignement_id', $ue->id)
                        ->where('annee_scolaire_id', $anneeScolaire->id)
                        ->where('validee', true)
                        ->first();

                    $typeValidation = $gratification ? 'rattrapage' : 'normale';
                }

                //Sauvegarde UE avec releve_note_id
                UeValidation::updateOrCreate(
                    [
                        'etudiant_id' => $etudiant->id,
                        'unite_enseignement_id' => $ue->id,
                        'annee_scolaire_id' => $anneeScolaire->id,
                        'periode_id' => $periode->id,
                    ],
                    [
                        'releve_note_id' => $releve->id,
                        'moyenne' => $moyenneUE,
                        'credit_obtenu' => $creditUEObtenu,
                        'validee' => $ueValidee,
                        'type_validation' => $typeValidation
                    ]
                );

                $coefficientUE = $ue->coefficient ?? 1;

                $sommeMoyennesPonderees += $moyenneUE * $coefficientUE;
                $totalCoefficients += $coefficientUE;

                if ($ueValidee) {
                    $totalCreditsValides += $creditUEObtenu;
                } else {
                    $totalCreditsNonValides += ($ue->credit ?? 0);
                }
            }

            //  Calcul moyenne générale
            $moyenneGenerale = $totalCoefficients > 0
                ? round($sommeMoyennesPonderees / $totalCoefficients, 2)
                : 0;

            //Mise à jour finale du relevé
            $releve->update([
                'moyenne_generale' => $moyenneGenerale,
                'total_credits_valides' => $totalCreditsValides,
                'total_credits_non_valides' => $totalCreditsNonValides,
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
        $evaluations = Evaluation::where('unite_valeur_id', $uv->id)
            ->whereHas('emploiDutemp', function ($q) use ($anneeScolaire, $periode) {
                $q->where('annee_scolaire_id', $anneeScolaire->id);
                // ->where('periode_id', $periode->id);
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

        foreach ($releve->ueValidations as $ueValidation) {

            $ue = $ueValidation->uniteEnseignement;

            // récupérer les UV de cette UE à partir du même relevé
            $uvValidations = $releve->uvValidations
                ->where('uniteValeur.unite_enseignement_id', $ue->id);

            $uvs = [];

            foreach ($uvValidations as $uvValidation) {

                $uv = $uvValidation->uniteValeur;

                $uvs[] = [
                    'uv' => $uv->nom,
                    'devoir' => number_format($uvValidation->note_devoir ?? 0, 2),
                    'examen' => number_format($uvValidation->note_examen ?? 0, 2),
                    'moyenne_uv' => number_format($uvValidation->moyenne, 2),
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
                'genre' => $etudiant->genre->value ?? 'M'
            ],
            'annee_scolaire' => $anneeScolaire->nom,
            'periode' => $periode->nom,
            'moyenne_generale' => number_format($releve->moyenne_generale, 2),
            'total_credits_valides' => $releve->total_credits_valides,
            'total_credits_non_valides' => $releve->total_credits_non_valides,
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
                        'uv' => $uv->nom,
                        'devoir' => number_format($uvValidation->note_devoir ?? 0, 2),
                        'examen' => number_format($uvValidation->note_examen ?? 0, 2),
                        'moyenne_uv' => number_format($uvValidation->moyenne, 2),
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
                'date_generation' => $releve->created_at->format('Y-m-d'),
                'moyenne_generale' => number_format($releve->moyenne_generale, 2),
                'total_credits_valides' => $releve->total_credits_valides,
                'total_credits_non_valides' => $releve->total_credits_non_valides,
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

                $uvValidations = $releve->uvValidations
                    ->where('uniteValeur.unite_enseignement_id', $ue->id);

                $uvs = [];

                foreach ($uvValidations as $uvValidation) {
                    $uv = $uvValidation->uniteValeur;

                    $uvs[] = [
                        'uv' => $uv->nom,
                        'devoir' => number_format($uvValidation->note_devoir ?? 0, 2),
                        'examen' => number_format($uvValidation->note_examen ?? 0, 2),
                        'moyenne_uv' => number_format($uvValidation->moyenne, 2),
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
                'annee_scolaire' => $releve->anneeScolaire->nom,
                'annee_scolaire_id' => $releve->annee_scolaire_id,
                'periode' => $releve->periode->nom,
                'periode_id' => $releve->periode_id,
                'date_generation' => $releve->created_at->format('Y-m-d H:i:s'),
                'moyenne_generale' => number_format($releve->moyenne_generale, 2),
                'total_credits_valides' => $releve->total_credits_valides,
                'total_credits_non_valides' => $releve->total_credits_non_valides,
                'ues_count' => count($releveGrouped),
                'ues' => $releveGrouped
            ];
        }

        return $relevesFormatted;
    }
}
