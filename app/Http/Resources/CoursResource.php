<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            
            // Informations générales
            'uv' => [
                'id' => $this->uv?->id,
                'nom' => $this->uv?->nom,
                'code' => $this->uv?->code,
                'credits' => $this->uv?->credits,
            ],
            
            // Horaires
            'debut' => $this->debut?->format('Y-m-d H:i:s'),
            'debut_formate' => $this->debut?->format('d/m/Y H:i'),
            'fin' => $this->fin?->format('Y-m-d H:i:s'),
            'fin_formate' => $this->fin?->format('d/m/Y H:i'),
            'duree_minutes' => $this->debut && $this->fin 
                ? round(($this->fin->timestamp - $this->debut->timestamp) / 60) 
                : null,
            
            // Type
            'type_programme' => $this->type_programme,
            
            // Récurrence
            'recurrence_type' => $this->recurrence_type,
            'recurrence_days' => $this->recurrence_days,
            'recurrence_days_libelle' => $this->formatJoursRecurrence(),
            'recurrence_end_date' => $this->recurrence_end_date?->format('Y-m-d'),
            
            // Relations
            'salle' => new SalleResource($this->whenLoaded('salle')),
            'groupe' => new GroupeResource($this->whenLoaded('group')),
            'enseignant' => new UserResource($this->whenLoaded('owner')),
            
            // Séances
            'seance_aujourdhui' => new SeanceResource($this->whenLoaded('seance_aujourdhui')),
            'seances' => SeanceResource::collection($this->whenLoaded('seances')),
            'nombre_seances' => $this->seances_count ?? $this->seances->count(),
            
            // Présences
            'a_des_presences' => $this->presences->isNotEmpty(),
            'nombre_presences' => $this->presences_count ?? $this->presences->count(),
            
            // Statut
            'statut_cours' => $this->statut_cours ?? $this->getStatutCours(),
            'est_modifiable' => $this->est_modifiable ?? $this->estModifiable(),
            
            // Métadonnées
            'details' => $this->details,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Formater les jours de récurrence
     */
    private function formatJoursRecurrence(): ?string
    {
        if (!$this->recurrence_days) {
            return null;
        }
        
        $joursMap = [
            'MO' => 'Lundi',
            'TU' => 'Mardi',
            'WE' => 'Mercredi',
            'TH' => 'Jeudi',
            'FR' => 'Vendredi',
            'SA' => 'Samedi',
            'SU' => 'Dimanche'
        ];
        
        $jours = explode(',', $this->recurrence_days);
        $joursLibelles = array_map(fn($j) => $joursMap[$j] ?? $j, $jours);
        
        return implode(', ', $joursLibelles);
    }

    /**
     * Obtenir le statut du cours
     */
    private function getStatutCours(): string
    {
        $now = now();
        
        if ($this->debut > $now) {
            return 'à_venir';
        }
        
        if ($this->debut <= $now && $this->fin >= $now) {
            return 'en_cours';
        }
        
        return 'termine';
    }

    /**
     * Vérifier si le cours est modifiable
     */
    private function estModifiable(): bool
    {
        // Si des présences existent déjà, non modifiable
        if ($this->presences->isNotEmpty()) {
            return false;
        }
        
        // Vérifier le jour pour les cours récurrents
        if ($this->recurrence_type === 'hebdomadaire' && $this->recurrence_days) {
            $joursMap = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
            $joursRecurrence = explode(',', $this->recurrence_days);
            $joursNumeriques = array_map(fn($j) => $joursMap[$j] ?? null, $joursRecurrence);
            
            $jourActuel = now()->dayOfWeek;
            
            if (!in_array($jourActuel, $joursNumeriques)) {
                return false;
            }
        }
        
        return true;
    }
}