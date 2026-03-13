<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtudiantAvecPresenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Récupérer la première présence (normalement une seule par requête)
        $presence = $this->presences->first();
        
        return [
            'id' => $this->id,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nom_complet' => $this->prenom . ' ' . $this->nom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'photo' => $this->photo,
            
            // Présence actuelle
            'presence' => $presence ? [
                'id' => $presence->id,
                'statut' => $presence->statut,
                'statut_libelle' => $presence->statut_libelle,
                'statut_couleur' => $presence->statut_couleur,
                'heure_arrivee' => $presence->heure_arrivee?->format('H:i'),
                'heure_depart' => $presence->heure_depart?->format('H:i'),
                'minutes_retard' => $presence->minutes_retard,
                'commentaire' => $presence->commentaire,
                'participation' => $presence->participation,
                'attitude' => $presence->attitude,
                'observations_comportement' => $presence->observations_comportement,
                'points_attention' => $presence->points_attention ?? [],
                'points_positifs' => $presence->points_positifs ?? [],
                'a_signalement' => (bool) $presence->a_signalement,
                'a_remonter_conseil' => (bool) $presence->a_remonter_conseil,
            ] : [
                'statut' => 'absent',
                'statut_libelle' => 'Absent',
                'statut_couleur' => 'red',
                'heure_arrivee' => null,
                'commentaire' => null,
            ],
            
            // Séance concernée
            'seance_id' => $request->get('seance_id'),
            
            // Statistiques globales de l'étudiant
            'statistiques' => [
                'total_presences' => $this->presences_globales_count ?? $this->presences()->count(),
                'taux_presence_global' => $this->calculerTauxPresenceGlobal(),
            ],
        ];
    }

    /**
     * Calculer le taux de présence global
     */
    private function calculerTauxPresenceGlobal(): ?float
    {
        $total = $this->presences_globales_count ?? $this->presences()->count();
        
        if ($total === 0) {
            return null;
        }
        
        $presents = $this->presences()
            ->whereIn('statut', ['present'])
            ->count();
        
        return round(($presents / $total) * 100, 2);
    }
}