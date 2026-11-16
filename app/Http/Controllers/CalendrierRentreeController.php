<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\CalendrierRentree;
use App\Models\Filiere;
use App\Models\FraisScolarite;
use App\Models\Niveau;
use App\Services\FraisEtudiantService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendrierRentreeController extends Controller
{
    /**
     * Recupere le calendrier académique complet pour une année scolaire.
     */
    public function index($anneeId)
    {
        $annee = AnneeScolaire::findOrFail($anneeId);
        $niveaux = Niveau::orderBy('ordre')->get();
        $filieres = Filiere::orderBy('nom')->get();

        $entries = CalendrierRentree::where('annee_scolaire_id', $annee->id)
            ->with(['niveau', 'filiere'])
            ->get();

        // Construire la liste structuree par niveau pour faciliter le rendu UI
        $resultNiveaux = $niveaux->map(function ($niveau) use ($entries, $annee) {
            $niveauEntry = $entries->first(function ($e) use ($niveau) {
                return $e->niveau_id == $niveau->id && is_null($e->filiere_id);
            });

            $filiereExceptions = $entries->filter(function ($e) use ($niveau) {
                return $e->niveau_id == $niveau->id && !is_null($e->filiere_id);
            })->values()->map(function ($e) {
                return [
                    'id' => $e->id,
                    'filiere_id' => $e->filiere_id,
                    'filiere_nom' => $e->filiere?->nom,
                    'filiere_code' => $e->filiere?->code,
                    'date_rentree' => $e->date_rentree ? Carbon::parse($e->date_rentree)->format('Y-m-d') : null,
                    'date_fin' => $e->date_fin ? Carbon::parse($e->date_fin)->format('Y-m-d') : null,
                ];
            });

            return [
                'niveau_id' => $niveau->id,
                'niveau_libelle' => $niveau->libelle,
                'ordre' => $niveau->ordre,
                'entry_id' => $niveauEntry?->id,
                'date_rentree' => $niveauEntry?->date_rentree 
                    ? Carbon::parse($niveauEntry->date_rentree)->format('Y-m-d') 
                    : ($annee->date_debut ? Carbon::parse($annee->date_debut)->format('Y-m-d') : null),
                'date_fin' => $niveauEntry?->date_fin 
                    ? Carbon::parse($niveauEntry->date_fin)->format('Y-m-d') 
                    : ($annee->date_fin ? Carbon::parse($annee->date_fin)->format('Y-m-d') : null),
                'is_custom' => !is_null($niveauEntry),
                'filiere_exceptions' => $filiereExceptions,
            ];
        });

        return response()->json([
            'annee' => [
                'id' => $annee->id,
                'nom' => $annee->nom,
                'date_debut' => $annee->date_debut ? Carbon::parse($annee->date_debut)->format('Y-m-d') : null,
                'date_fin' => $annee->date_fin ? Carbon::parse($annee->date_fin)->format('Y-m-d') : null,
                'active' => (bool) $annee->active,
            ],
            'niveaux' => $resultNiveaux,
            'filieres' => $filieres->map(fn($f) => [
                'id' => $f->id,
                'nom' => $f->nom,
                'code' => $f->code,
            ]),
        ]);
    }

    /**
     * Enregistre ou met a jour le calendrier des rentrées pour l'année scolaire.
     */
    public function save(Request $request, $anneeId, FraisEtudiantService $fraisEtudiantService)
    {
        $annee = AnneeScolaire::findOrFail($anneeId);

        $request->validate([
            'entries' => 'nullable|array',
            'entries.*.niveau_id' => 'required|exists:niveaux,id',
            'entries.*.filiere_id' => 'nullable|exists:filieres,id',
            'entries.*.date_rentree' => 'required|date',
            'entries.*.date_fin' => 'nullable|date',
            'entries.*.description' => 'nullable|string',
            'reset_niveaux' => 'nullable|array',
            'reset_niveaux.*' => 'integer|exists:niveaux,id',
            'sync_echeances' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($request, $annee, $fraisEtudiantService) {
            // 1. Supprimer les exceptions pour les niveaux rétablis sur la rentrée générale
            if ($request->has('reset_niveaux') && is_array($request->input('reset_niveaux'))) {
                CalendrierRentree::where('annee_scolaire_id', $annee->id)
                    ->whereIn('niveau_id', $request->input('reset_niveaux'))
                    ->whereNull('filiere_id')
                    ->delete();
            }

            // 2. Enregistrer ou mettre a jour les dates personnalisées
            $savedCount = 0;
            $entries = $request->input('entries', []);
            if (is_array($entries)) {
                foreach ($entries as $entryData) {
                    CalendrierRentree::updateOrCreate(
                        [
                            'annee_scolaire_id' => $annee->id,
                            'niveau_id' => $entryData['niveau_id'],
                            'filiere_id' => !empty($entryData['filiere_id']) ? $entryData['filiere_id'] : null,
                        ],
                        [
                            'date_rentree' => $entryData['date_rentree'],
                            'date_fin' => !empty($entryData['date_fin']) ? $entryData['date_fin'] : null,
                            'description' => $entryData['description'] ?? null,
                        ]
                    );
                    $savedCount++;
                }
            }

            $syncResult = null;
            if ($request->boolean('sync_echeances', true)) {
                // Régénérer les tranches des frais de cette année scolaire
                $fraisList = FraisScolarite::withoutGlobalScopes()
                    ->where('annee_scolaire_id', $annee->id)
                    ->get();

                foreach ($fraisList as $frais) {
                    if ($frais->frequence) {
                        $frais->tranchepaiement()->delete();
                        $this->generateTranchesFromCalendar($frais, $annee);
                    }
                }

                // Synchroniser les contrats et échéances des étudiants
                $syncResult = $fraisEtudiantService->synchroniserToutesEcheancesAnnee($annee->id);
            }

            return response()->json([
                'message' => "Calendrier académique enregistré avec succès ({$savedCount} promotion(s) configurée(s)).",
                'sync_result' => $syncResult,
            ]);
        });
    }

    /**
     * Supprime une exception de rentrée pour revenir à la date par défaut.
     */
    public function destroyEntry($anneeId, $entryId)
    {
        $entry = CalendrierRentree::where('annee_scolaire_id', $anneeId)->findOrFail($entryId);
        $entry->delete();

        return response()->json([
            'message' => "Configuration supprimée. La date par défaut s'applique désormais.",
        ]);
    }

    /**
     * Génère automatiquement les tranches d'un frais de scolarité à partir du calendrier officiel.
     */
    private function generateTranchesFromCalendar(FraisScolarite $frais, AnneeScolaire $annee)
    {
        $dateDebut = CalendrierRentree::resolveDateRentree($annee->id, $frais->niveau_id, $frais->filiere_id);
        if (!$dateDebut) {
            $dateDebut = $annee->date_debut ? Carbon::parse($annee->date_debut) : Carbon::now();
        }

        $frequency = $frais->frequence ?: 'bimestriel';
        $nbTranches = match($frequency) {
            'annuel' => 1,
            'trimestriel' => 3,
            'bimestriel' => 4,
            default => 4
        };

        $intervalle = match($frequency) {
            'annuel' => 12,
            'trimestriel' => 3,
            'bimestriel' => 2,
            default => 2
        };

        $totalMontant = $frais->montant;
        $montantTranche = floor($totalMontant / $nbTranches);
        $reliquat = $totalMontant % $nbTranches;

        for ($i = 0; $i < $nbTranches; $i++) {
            $echeance = (clone $dateDebut)->addMonths($i * $intervalle);

            \App\Models\TranchePaiement::create([
                'frais_scolarite_id' => $frais->id,
                'annee_scolaire_id' => $annee->id,
                'libelle' => 'Tranche ' . ($i + 1),
                'montant' => ($i === 0) ? ($montantTranche + $reliquat) : $montantTranche,
                'date_limite' => $echeance->format('Y-m-d'),
                'status' => true,
            ]);
        }
    }
}

