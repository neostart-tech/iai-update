<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeanceResource extends JsonResource
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
            'date_seance' => $this->date_seance->format('Y-m-d'),
            'date_formatee' => $this->date_seance->format('d/m/Y'),
            'jour_semaine' => $this->date_seance->locale('fr')->dayName,
            
            // Horaires prévus
            'heure_debut_prevue' => $this->heure_debut_prevue?->format('H:i'),
            'heure_fin_prevue' => $this->heure_fin_prevue?->format('H:i'),
            
            // Horaires réels
            'heure_debut_reelle' => $this->heure_debut_reelle?->format('H:i'),
            'heure_fin_reelle' => $this->heure_fin_reelle?->format('H:i'),
            'duree_reelle_minutes' => $this->calculerDureeReelle(),
            
            // Statut
            'statut' => $this->statut,
            'statut_libelle' => $this->getStatutLibelle(),
            'statut_couleur' => $this->getStatutCouleur(),
            'est_terminee' => $this->estTerminee(),
            'est_en_cours' => $this->estEnCours(),
            'est_annulee' => $this->estAnnulee(),
            
            // Informations
            'motif_annulation' => $this->motif_annulation,
            'notes_seance' => $this->notes_seance,
            
            // Statistiques
            'statistiques' => [
                'total_etudiants' => $this->presences_count ?? $this->presences->count(),
                'presents' => $this->present_count ?? $this->presences->whereIn('statut', ['present'])->count(),
                'absents' => $this->absent_count ?? $this->presences->whereIn('statut', ['absent', 'absent_justifie'])->count(),
                'retards' => $this->retard_count ?? $this->presences->whereIn('statut', ['retard', 'retard_justifie'])->count(),
                'taux_presence' => $this->calculerTauxPresence(),
            ],
            
            // Relations
            'cours' => new CoursResource($this->whenLoaded('emploiDuTemps')),
            'remplacant' => new UserResource($this->whenLoaded('remplacant')),
            'salle_reelle' => new SalleResource($this->whenLoaded('salleReelle')),
            'presences' => PresenceResource::collection($this->whenLoaded('presences')),
            
            // Métadonnées
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Calculer la durée réelle de la séance
     */
    private function calculerDureeReelle(): ?int
    {
        if (!$this->heure_debut_reelle || !$this->heure_fin_reelle) {
            return null;
        }
        
        $debut = strtotime($this->heure_debut_reelle);
        $fin = strtotime($this->heure_fin_reelle);
        
        return round(($fin - $debut) / 60);
    }

    /**
     * Calculer le taux de présence
     */
    private function calculerTauxPresence(): ?float
    {
        $total = $this->presences_count ?? $this->presences->count();
        
        if ($total === 0) {
            return null;
        }
        
        $presents = $this->present_count ?? $this->presences->whereIn('statut', ['present'])->count();
        
        return round(($presents / $total) * 100, 2);
    }

    /**
     * Obtenir le libellé du statut
     */
    private function getStatutLibelle(): string
    {
        $libelles = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'annule' => 'Annulé',
            'reporte' => 'Reporté',
            'rattrapage' => 'Rattrapage'
        ];
        
        return $libelles[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la couleur du statut
     */
    private function getStatutCouleur(): string
    {
        $couleurs = [
            'planifie' => 'blue',
            'en_cours' => 'green',
            'termine' => 'gray',
            'annule' => 'red',
            'reporte' => 'orange',
            'rattrapage' => 'purple'
        ];
        
        return $couleurs[$this->statut] ?? 'gray';
    }
}