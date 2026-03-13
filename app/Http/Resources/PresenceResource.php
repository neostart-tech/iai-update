<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresenceResource extends JsonResource
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
            'statut' => $this->statut,
            'statut_libelle' => $this->statut_libelle,
            'statut_couleur' => $this->statut_couleur,
            'date' => $this->date->format('Y-m-d'),
            'date_formatee' => $this->date->format('d/m/Y'),
            'heure_arrivee' => $this->heure_arrivee ? $this->heure_arrivee->format('H:i') : null,
            'heure_depart' => $this->heure_depart ? $this->heure_depart->format('H:i') : null,
            'minutes_retard' => $this->minutes_retard,
            'commentaire' => $this->commentaire,
            
            // Comportement
            'participation' => $this->participation,
            'participation_libelle' => $this->getParticipationLibelle(),
            'attitude' => $this->attitude,
            'attitude_libelle' => $this->getAttitudeLibelle(),
            'observations_comportement' => $this->observations_comportement,
            'points_attention' => $this->points_attention ?? [],
            'points_positifs' => $this->points_positifs ?? [],
            
            // Alertes
            'a_signalement' => (bool) $this->a_signalement,
            'a_remonter_conseil' => (bool) $this->a_remonter_conseil,
            
            // Validation
            'needs_validation' => (bool) $this->needs_validation,
            'validation_statut' => $this->validation_statut,
            'est_validee' => $this->estValidee(),
            'validated_at' => $this->validated_at?->format('d/m/Y H:i'),
            
            // Notifications
            'notification_envoyee' => (bool) $this->notification_envoyee,
            'parent_informe' => (bool) $this->parent_informe,
            
            // Relations
            'etudiant' => new EtudiantRessource($this->whenLoaded('etudiant')),
            'seance' => new SeanceResource($this->whenLoaded('seance')),
            'justificatif' => new JustificatifResource($this->whenLoaded('justificatif')),
            
            // Métadonnées
            'source' => $this->source,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Obtenir le libellé de la participation
     */
    private function getParticipationLibelle(): ?string
    {
        $libelles = [
            'excellente' => 'Excellente',
            'bonne' => 'Bonne',
            'moyenne' => 'Moyenne',
            'faible' => 'Faible',
            'nulle' => 'Nulle',
            'non_concerné' => 'Non concerné'
        ];
        
        return $libelles[$this->participation] ?? $this->participation;
    }

    /**
     * Obtenir le libellé de l'attitude
     */
    private function getAttitudeLibelle(): ?string
    {
        $libelles = [
            'exemplaire' => 'Exemplaire',
            'correcte' => 'Correcte',
            'a_surveiller' => 'À surveiller',
            'problematique' => 'Problématique',
            'perturbateur' => 'Perturbateur'
        ];
        
        return $libelles[$this->attitude] ?? $this->attitude;
    }
}