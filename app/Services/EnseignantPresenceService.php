<?php
// app/Services/EnseignantPresenceService.php

namespace App\Services;

use App\Models\EnseignantPresence;
use App\Models\EmploiDuTemp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnseignantPresenceService
{
    /**
     * Vérifie si un cours a lieu à une date donnée
     * Gère les cours récurrents et non récurrents
     * 
     * @param EmploiDuTemp $emploi Le cours à vérifier
     * @param string|Carbon $date La date à vérifier
     * @return bool
     */
   public function verifierJourCours($emploi, $date): bool
{
    $dateCarbon = $date instanceof Carbon ? $date : Carbon::parse($date);
    
    // CAS 1: Cours non récurrent
    if ($emploi->recurrence_type === 'aucune' || !$emploi->recurrence_type) {
        $dateDebut = Carbon::parse($emploi->debut)->toDateString();
        return $dateDebut === $dateCarbon->toDateString();
    }
    
    // CAS 2: Cours récurrent hebdomadaire
    if ($emploi->recurrence_type === 'hebdomadaire') {
        return $this->verifierCoursHebdomadaireCorrige($emploi, $dateCarbon);
    }
    
    return false;
}
    
    /**
     * Vérifie un cours hebdomadaire
     */
   private function verifierCoursHebdomadaireCorrige($emploi, Carbon $date): bool
{
    try {
        // 1. Vérifier que la date est après le début du cours
        $debut = Carbon::parse($emploi->debut);
        if ($date->lt($debut)) {
            return false;
        }
        
        // 2. Vérifier la date de fin de récurrence
        if ($emploi->recurrence_end_date) {
            $fin = Carbon::parse($emploi->recurrence_end_date);
            if ($date->gt($fin)) {
                return false;
            }
        }
        
        // 3. Vérifier le jour de la semaine
        if (!$emploi->recurrence_days) {
            return false;
        }
        
        // Vos codes: MO, TU, WE, TH, FR, SA, SU
        $joursPrevu = explode(',', $emploi->recurrence_days);
        
        // Utiliser dayOfWeek (0 = Dimanche, 1 = Lundi, 2 = Mardi, etc.)
        $jourIndex = $date->dayOfWeek;
        
        // Mapping INDEX -> CODE
        $indexVersCode = [
            0 => 'SU', // Dimanche
            1 => 'MO', // Lundi
            2 => 'TU', // Mardi
            3 => 'WE', // Mercredi
            4 => 'TH', // Jeudi
            5 => 'FR', // Vendredi
            6 => 'SA'  // Samedi
        ];
        
        $jourCode = $indexVersCode[$jourIndex] ?? null;
        
        // LOG DE DÉBOGAGE
        // \Log::info('Vérification cours hebdomadaire', [
        //     'date' => $date->format('Y-m-d'),
        //     'jour_index' => $jourIndex,
        //     'jour_code' => $jourCode,
        //     'jours_prevu' => $joursPrevu,
        //     'resultat' => $jourCode && in_array($jourCode, $joursPrevu)
        // ]);
        
        return $jourCode && in_array($jourCode, $joursPrevu);
        
    } catch (\Exception $e) {
        // \Log::error('Erreur vérification cours hebdomadaire: ' . $e->getMessage());
        return false;
    }
}
    /**
     * Récupère le volume horaire total d'une UV
     */
    public function getVolumeHoraireTotal($uv): int
    {
        if (!$uv) return 0;
        return (int)($uv->volume_horaire ?? $uv->ec ?? 0);
    }
    
    /**
     * Calcule le total des minutes déjà effectuées pour une UV
     */
    public function getHeuresEffectueesUV(int $enseignantId, int $uvId, ?int $excludePresenceId = null): int
    {
        try {
            $query = EnseignantPresence::where('enseignant_id', $enseignantId)
                ->where('est_termine', true)
                ->whereHas('emploiDuTemps', function($q) use ($uvId) {
                    $q->where('uv_id', $uvId);
                });
                
            if ($excludePresenceId) {
                $query->where('id', '!=', $excludePresenceId);
            }
            
            return $query->sum('duree_calculee_minutes') ?? 0;
            
        } catch (\Exception $e) {
            Log::error('Erreur calcul heures effectuées: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Détermine le statut (présent/retard)
     */
    public function determinerStatut(string $heureArrivee, string $heureDebutCours): string
    {
        try {
            $arrivee = Carbon::parse($heureArrivee);
            $debut = Carbon::parse($heureDebutCours);
            
            if ($arrivee->gt($debut)) {
                return 'retard';
            }
            
            return 'present';
            
        } catch (\Exception $e) {
            Log::error('Erreur détermination statut: ' . $e->getMessage());
            return 'present';
        }
    }
    
    /**
     * Calcule la durée et gère la troncature
     */
    public function calculerDureeAvecTroncature(
        int $minutesEcoulees,
        int $minutesRestantesAvant,
        string $heureArrivee
    ): array {
        $dureeCalculee = $minutesEcoulees;
        $typePointage = 'depart_complete';
        $heureDepartReelle = null;
        $message = 'Départ enregistré avec succès';
        
        if ($minutesRestantesAvant <= 0) {
            $dureeCalculee = 0;
            $typePointage = 'depart_tronque';
            $message = 'Volume horaire total déjà atteint. Aucune heure supplémentaire comptabilisée.';
        }
        elseif ($minutesEcoulees > $minutesRestantesAvant) {
            $dureeCalculee = $minutesRestantesAvant;
            $typePointage = 'depart_tronque';
            
            $heureDepartReelle = Carbon::parse($heureArrivee)
                ->addMinutes($minutesEcoulees)
                ->format('H:i:s');
            
            $message = "La durée a été limitée à " . round($minutesRestantesAvant / 60, 2) . 
                       "h pour respecter le volume horaire total de l'UV.";
        }
        
        return [
            'duree_calculee' => $dureeCalculee,
            'type_pointage' => $typePointage,
            'heure_depart_reelle' => $heureDepartReelle,
            'message' => $message
        ];
    }
    
    /**
     * Vérifie si le départ peut être enregistré (minimum 15 minutes)
     */
    public function peutEnregistrerDepart(string $heureArrivee): array
    {
        try {
            $arrivee = Carbon::parse($heureArrivee);
            $maintenant = Carbon::now();
            $minutesEcoulees = $arrivee->diffInMinutes($maintenant);
            
            if ($minutesEcoulees >= 15) {
                return [
                    'peut' => true,
                    'minutes_ecoulees' => $minutesEcoulees,
                    'message' => 'Vous pouvez enregistrer le départ'
                ];
            }
            
            $tempsRestant = 15 - $minutesEcoulees;
            
            return [
                'peut' => false,
                'minutes_ecoulees' => $minutesEcoulees,
                'temps_restant' => $tempsRestant,
                'message' => "Encore {$tempsRestant} minute(s) avant de pouvoir enregistrer le départ"
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur vérification départ: ' . $e->getMessage());
            return [
                'peut' => false,
                'message' => 'Erreur de vérification'
            ];
        }
    }
}