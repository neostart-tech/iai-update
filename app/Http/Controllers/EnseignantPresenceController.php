<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnseignantPresence;
use App\Models\EmploiDuTemp;
use App\Models\UniteValeur;
use App\Services\EnseignantPresenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EnseignantPresenceController extends Controller
{
    protected $presenceService;
    
    public function __construct(EnseignantPresenceService $presenceService)
    {
        $this->presenceService = $presenceService;
    }

    public function enregistrerArrivee(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
                'enseignant_id' => 'required|exists:users,id',
            ]);
            
            $emploiId = $request->emploi_du_temps_id;
            $enseignantId = $request->enseignant_id;
            
            $emploi = EmploiDuTemp::with(['uv', 'group', 'salle', 'owner'])
                ->find($emploiId);
                
            if (!$emploi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Séance introuvable'
                ], 404);
            }
            
            $dateCours = now()->toDateString();
            
            Log::info('=== TENTATIVE DE POINTAGE ENSEIGNANT ===', [
                'emploi_id' => $emploiId,
                'enseignant_id' => $enseignantId,
                'uv_nom' => $emploi->uv->nom ?? 'N/A',
                'recurrence_type' => $emploi->recurrence_type,
                'recurrence_days' => $emploi->recurrence_days,
                'date_cours' => $dateCours,
                'jour_semaine' => now()->locale('fr')->dayName,
                'jour_php' => strtoupper(now()->format('D')),
                'heure_actuelle' => now()->format('H:i:s')
            ]);
            
            // if (!$this->presenceService->verifierJourCours($emploi, $dateCours)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "Ce cours n'est pas prévu aujourd'hui",
            //         'details' => [
            //             'date' => $dateCours,
            //             'recurrence_type' => $emploi->recurrence_type,
            //             'recurrence_days' => $emploi->recurrence_days,
            //             'jour_actuel' => now()->locale('fr')->dayName
            //         ]
            //     ], 422);
            // }
            
            $existingPresence = EnseignantPresence::where('emploi_du_temps_id', $emploiId)
                ->whereDate('date_cours', $dateCours)
                ->first();
            
            if ($existingPresence) {
                if ($existingPresence->est_termine) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Vous avez déjà pointé pour ce cours aujourd\'hui',
                        'deja_termine' => true,
                        'presence' => $existingPresence
                    ], 200);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Une présence est déjà en cours pour ce cours',
                    'en_cours' => true,
                    'presence' => $existingPresence
                ], 200);
            }
            
            // ÉTAPE 3: Préparer les données d'arrivée
            $maintenant = now();
            $heureArrivee = $maintenant->format('H:i:s');
            $debutCours = Carbon::parse($emploi->debut)->format('H:i:s');
            $finCours = Carbon::parse($emploi->fin)->format('H:i:s');
            
            $statut = $this->presenceService->determinerStatut($heureArrivee, $debutCours);
            
            // ÉTAPE 4: Vérifier le volume horaire
            $volumeHoraireTotal = $this->presenceService->getVolumeHoraireTotal($emploi->uv);
            $heuresEffectuees = $this->presenceService->getHeuresEffectueesUV(
                $enseignantId, 
                $emploi->uv_id
            );
            
            $minutesRestantes = ($volumeHoraireTotal * 60) - $heuresEffectuees;
            
            // ÉTAPE 5: Créer la présence
            $presence = EnseignantPresence::create([
                'emploi_du_temps_id' => $emploiId,
                'date_cours' => $dateCours,
                'heure_debut_prevue' => $debutCours,
                'heure_fin_prevue' => $finCours,
                'enseignant_id' => $enseignantId,
                'statut' => $statut,
                'heure_arrivee' => $heureArrivee,
                'type_pointage' => 'arrivee',
                'arrivee_enregistree_at' => $maintenant,
                'est_termine' => false,
                'meta_data' => [
                    'minutes_restantes_avant_seance' => $minutesRestantes,
                    'volume_horaire_total' => $volumeHoraireTotal,
                    'heures_deja_effectuees' => $heuresEffectuees / 60,
                    'debut_cours_original' => $emploi->debut,
                    'fin_cours_original' => $emploi->fin,
                    'recurrence_type' => $emploi->recurrence_type,
                    'recurrence_days' => $emploi->recurrence_days,
                    'jour_pointage' => now()->locale('fr')->dayName
                ]
            ]);
            
            DB::commit();

            Log::info('Arrivée enregistrée avec succès', ['presence_id' => $presence->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Arrivée enregistrée avec succès',
                'presence' => $presence,
                'heure_arrivee' => $heureArrivee,
                'statut' => $statut,
                'date_cours' => $dateCours,
                'volume_horaire' => [
                    'total' => $volumeHoraireTotal,
                    'effectue' => round($heuresEffectuees / 60, 2),
                    'restant' => round($minutesRestantes / 60, 2),
                ]
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur enregistrement arrivée: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function enregistrerDepart($emploiDuTempsId)
    {
        try {
            DB::beginTransaction();
            
            $dateCours = now()->toDateString();
            
            $presence = EnseignantPresence::with('emploiDuTemps.uv')
                ->where('emploi_du_temps_id', $emploiDuTempsId)
                ->whereDate('date_cours', $dateCours)
                ->where('est_termine', false)
                ->first();
            
            if (!$presence) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune présence en cours trouvée pour ce cours aujourd\'hui'
                ], 404);
            }
            
            $verificationDepart = $this->presenceService->peutEnregistrerDepart($presence->heure_arrivee);
            
            if (!$verificationDepart['peut']) {
                return response()->json([
                    'success' => false,
                    'message' => $verificationDepart['message'],
                    'temps_restant' => $verificationDepart['temps_restant'] ?? null
                ], 422);
            }
            
            $maintenant = now();
            $heureDepart = $maintenant->format('H:i:s');
            $arrivee = Carbon::parse($presence->heure_arrivee);
            $minutesEcoulees = $arrivee->diffInMinutes($maintenant);
            
            $emploi = $presence->emploiDuTemps;
            $volumeHoraireTotal = $this->presenceService->getVolumeHoraireTotal($emploi->uv);
            $heuresEffectueesAvant = $this->presenceService->getHeuresEffectueesUV(
                $presence->enseignant_id, 
                $emploi->uv_id,
                $presence->id
            );
            
            $minutesRestantesAvant = ($volumeHoraireTotal * 60) - $heuresEffectueesAvant;
            
            $calcul = $this->presenceService->calculerDureeAvecTroncature(
                $minutesEcoulees,
                $minutesRestantesAvant,
                $presence->heure_arrivee
            );
            
            $presence->update([
                'heure_depart' => $heureDepart,
                'heure_depart_reelle' => $calcul['heure_depart_reelle'],
                'duree_reelle_minutes' => $minutesEcoulees,
                'duree_calculee_minutes' => $calcul['duree_calculee'],
                'type_pointage' => $calcul['type_pointage'],
                'depart_enregistree_at' => $maintenant,
                'est_termine' => true,
                'meta_data' => array_merge($presence->meta_data ?? [], [
                    'volume_horaire' => [
                        'total_heures' => $volumeHoraireTotal,
                        'effectue_avant' => round($heuresEffectueesAvant / 60, 2),
                        'restant_avant' => round($minutesRestantesAvant / 60, 2),
                        'duree_reelle' => round($minutesEcoulees / 60, 2),
                        'duree_calculee' => round($calcul['duree_calculee'] / 60, 2),
                        'restant_apres' => round(($minutesRestantesAvant - $calcul['duree_calculee']) / 60, 2)
                    ]
                ])
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $calcul['message'],
                'presence' => $presence,
                'details' => [
                    'heure_arrivee' => $presence->heure_arrivee,
                    'heure_depart' => $heureDepart,
                    'duree_reelle' => round($minutesEcoulees / 60, 2) . 'h',
                    'duree_calculee' => round($calcul['duree_calculee'] / 60, 2) . 'h',
                    'type_pointage' => $calcul['type_pointage'],
                ]
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur enregistrement départ: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du départ: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * =====================================================
     * ROUTE 3: GET /api/enseignant-presence/check/{emploiDuTempsId}
     * =====================================================
     */
    public function checkPresence($emploiDuTempsId)
    {
        try {
            $dateCours = now()->toDateString();
            
            $presence = EnseignantPresence::with('emploiDuTemps.uv')
                ->where('emploi_du_temps_id', $emploiDuTempsId)
                ->whereDate('date_cours', $dateCours)
                ->first();
            
            if (!$presence) {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'en_cours' => false,
                    'terminee' => false,
                    'message' => 'Aucune présence enregistrée pour aujourd\'hui'
                ]);
            }
            
            $volumeInfo = null;
            if ($presence->est_termine && $presence->emploiDuTemps->uv) {
                $heuresEffectuees = $this->presenceService->getHeuresEffectueesUV(
                    $presence->enseignant_id,
                    $presence->emploiDuTemps->uv_id
                );
                
                $volumeTotal = $this->presenceService->getVolumeHoraireTotal($presence->emploiDuTemps->uv);
                
                $volumeInfo = [
                    'total' => $volumeTotal,
                    'effectue' => round($heuresEffectuees / 60, 2),
                    'restant' => round(($volumeTotal * 60 - $heuresEffectuees) / 60, 2)
                ];
            }
            
            $peutEnregistrerDepart = false;
            if (!$presence->est_termine) {
                $verif = $this->presenceService->peutEnregistrerDepart($presence->heure_arrivee);
                $peutEnregistrerDepart = $verif['peut'];
            }
            
            return response()->json([
                'success' => true,
                'exists' => true,
                'en_cours' => !$presence->est_termine,
                'terminee' => $presence->est_termine,
                'presence' => $presence,
                'heure_arrivee' => $presence->heure_arrivee,
                'heure_depart' => $presence->heure_depart,
                'statut' => $presence->statut,
                'duree' => $presence->duree_calculee_heures,
                'date_cours' => $presence->date_cours,
                'volume_horaire' => $volumeInfo,
                'peut_enregistrer_depart' => $peutEnregistrerDepart
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur check présence: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ], 500);
        }
    }
    
    /**
     * =====================================================
     * ROUTE 4: GET /api/enseignant-presence/temps-restant/{emploiDuTempsId}
     * =====================================================
     */
    public function tempsRestantDepart($emploiDuTempsId)
    {
        try {
            $dateCours = now()->toDateString();
            
            $presence = EnseignantPresence::where('emploi_du_temps_id', $emploiDuTempsId)
                ->whereDate('date_cours', $dateCours)
                ->where('est_termine', false)
                ->first();
            
            if (!$presence) {
                return response()->json([
                    'success' => true,
                    'peut_enregistrer' => false,
                    'message' => 'Aucune présence en cours'
                ]);
            }
            
            $verification = $this->presenceService->peutEnregistrerDepart($presence->heure_arrivee);
            
            return response()->json([
                'success' => true,
                'peut_enregistrer' => $verification['peut'],
                'temps_restant' => $verification['temps_restant'] ?? 0,
                'minutes_ecoulees' => $verification['minutes_ecoulees'] ?? 0,
                'message' => $verification['message']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur temps restant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul'
            ], 500);
        }
    }
    
    /**
     * =====================================================
     * ROUTE 5: GET /api/enseignant-presence/recap/{enseignantId}/{uvId}
     * =====================================================
     */
    public function getRecapHeures($enseignantId, $uvId)
    {
        try {
            $uv = UniteValeur::find($uvId);
            if (!$uv) {
                return response()->json([
                    'success' => false,
                    'message' => 'UV non trouvée'
                ], 404);
            }
            
            $volumeHoraireTotal = $this->presenceService->getVolumeHoraireTotal($uv);
            $heuresEffectuees = $this->presenceService->getHeuresEffectueesUV($enseignantId, $uvId);
            
            $presences = EnseignantPresence::with('emploiDuTemps')
                ->where('enseignant_id', $enseignantId)
                ->where('est_termine', true)
                ->whereHas('emploiDuTemps', function($q) use ($uvId) {
                    $q->where('uv_id', $uvId);
                })
                ->orderBy('date_cours', 'desc')
                ->get()
                ->map(function($p) {
                    return [
                        'id' => $p->id,
                        'date' => $p->date_cours ? Carbon::parse($p->date_cours)->format('d/m/Y') : $p->created_at->format('d/m/Y'),
                        'heure_arrivee' => $p->heure_arrivee,
                        'heure_depart' => $p->heure_depart,
                        'duree' => $p->duree_calculee_heures . 'h',
                        'statut' => $p->statut,
                    ];
                });
            
            $pourcentage = $volumeHoraireTotal > 0 
                ? round(($heuresEffectuees / 60) / $volumeHoraireTotal * 100, 2) 
                : 0;
            
            return response()->json([
                'success' => true,
                'uv' => [
                    'id' => $uv->id,
                    'nom' => $uv->nom,
                ],
                'volume_horaire' => [
                    'total' => $volumeHoraireTotal,
                    'effectue' => round($heuresEffectuees / 60, 2),
                    'restant' => round(($volumeHoraireTotal * 60 - $heuresEffectuees) / 60, 2),
                    'pourcentage' => $pourcentage
                ],
                'presences' => $presences,
                'total_seances' => $presences->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur récapitulatif: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du récapitulatif'
            ], 500);
        }
    }
    
    /**
     * =====================================================
     * ROUTE 6: GET /api/enseignant-presence/cours-du-jour
     * =====================================================
     */
    public function mesCoursDuJour(Request $request)
    {
        try {
            $user = auth()->user();
            $date = $request->get('date', now()->toDateString());
            $dateCarbon = Carbon::parse($date);
            
            $cours = EmploiDuTemp::with(['uv', 'group', 'salle'])
                ->where('owner_id', $user->id)
                ->where('type_programme', 'Cours')
                ->get()
                ->filter(function($cours) use ($dateCarbon) {
                    return $this->presenceService->verifierJourCours($cours, $dateCarbon);
                })
                ->map(function($cours) use ($date) {
                    $presence = EnseignantPresence::where('emploi_du_temps_id', $cours->id)
                        ->whereDate('date_cours', $date)
                        ->first();
                    
                    $cours->pointage = $presence ? [
                        'id' => $presence->id,
                        'statut' => $presence->statut,
                        'est_termine' => $presence->est_termine,
                        'heure_arrivee' => $presence->heure_arrivee,
                        'heure_depart' => $presence->heure_depart,
                        'duree' => $presence->duree_calculee_heures,
                    ] : null;
                    
                    return $cours;
                })
                ->values();
            
            return response()->json([
                'success' => true,
                'date' => $date,
                'cours' => $cours,
                'total' => $cours->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur cours du jour: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des cours'
            ], 500);
        }
    }
}