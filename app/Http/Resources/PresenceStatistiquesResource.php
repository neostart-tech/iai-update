<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresenceStatistiquesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Statistiques générales
            'general' => [
                'total_etudiants' => $this['total_etudiants'] ?? 0,
                'total_presences' => $this['total_presences'] ?? 0,
                'taux_presence_moyen' => $this['taux_presence_moyen'] ?? 0,
                'taux_absent_moyen' => $this['taux_absent_moyen'] ?? 0,
            ],
            
            // Répartition par statut
            'repartition' => [
                'presents' => $this['presents'] ?? 0,
                'absents' => $this['absents'] ?? 0,
                'absents_justifies' => $this['absents_justifies'] ?? 0,
                'retards' => $this['retards'] ?? 0,
                'retards_justifies' => $this['retards_justifies'] ?? 0,
                'dispenses' => $this['dispenses'] ?? 0,
                'exclus' => $this['exclus'] ?? 0,
            ],
            
            // Évolution dans le temps
            'evolution' => [
                'par_jour' => $this['par_jour'] ?? [],
                'par_semaine' => $this['par_semaine'] ?? [],
                'par_mois' => $this['par_mois'] ?? [],
            ],
            
            // Comportement
            'comportement' => [
                'participations' => [
                    'excellente' => $this['participation_excellente'] ?? 0,
                    'bonne' => $this['participation_bonne'] ?? 0,
                    'moyenne' => $this['participation_moyenne'] ?? 0,
                    'faible' => $this['participation_faible'] ?? 0,
                ],
                'attitudes' => [
                    'exemplaire' => $this['attitude_exemplaire'] ?? 0,
                    'correcte' => $this['attitude_correcte'] ?? 0,
                    'a_surveiller' => $this['attitude_a_surveiller'] ?? 0,
                    'problematique' => $this['attitude_problematique'] ?? 0,
                ],
                'signalements' => $this['signalements'] ?? 0,
            ],
            
            // Par cours
            'par_cours' => $this['par_cours'] ?? [],
            
            // Par étudiant (top/alertes)
            'etudiants' => [
                'meilleurs' => $this['meilleurs_etudiants'] ?? [],
                'a_surveiller' => $this['etudiants_a_surveiller'] ?? [],
                'absent_repetitif' => $this['absents_repetitifs'] ?? [],
            ],
            
            // Période
            'periode' => [
                'debut' => $this['periode_debut'] ?? null,
                'fin' => $this['periode_fin'] ?? null,
            ],
        ];
    }
}