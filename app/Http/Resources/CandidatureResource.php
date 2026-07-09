<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidatureResource extends JsonResource
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
            'nom' => $this->nom,
            'nom_jeune_fille' => $this->nom_jeune_fille,
            'prenom' => $this->prenom,
            'numero_table' => $this->numero_table,
            'annee_bac' => $this->annee_bac,
            'serie' => $this->serie,
            'lettre_motivation' => $this->lettre_motivation,
            'genre' => $this->genre,
            'date_naissance' => $this->date_naissance,
            'lieu_naissance' => $this->lieu_naissance,
            'nationalite' => $this->nationalite,
            'tel' => $this->tel,
            'tel2' => $this->tel2,
            'tel3' => $this->tel3,
            'bp' => $this->bp,
            'fax' => $this->fax,
            'hobbit' => $this->hobbit,
            'email' => $this->email,
            'statut' => $this->computeStatut(),
            'dossier_valide' => (bool) $this->dossier_valide,
            'validation_date' => $this->validation_date,
            'frais_paye' => (bool) $this->frais_paye,
            'frai_paye_date' => $this->frai_paye_date,
            'participation' => (bool) $this->participation,
            'participation_date' => $this->participation_date,
            'admission' => (bool) $this->admission,
            'admission_date' => $this->admission_date,
            'motif' => $this->motif,
            'rectification_expected' => (bool) $this->rectification_expected,
            'slug' => $this->slug,
            'code' => $this->code,
            'matricule_concours' => $this->matricule_concours,
            'concours_session_id' => $this->concours_session_id,
            'avec_epreuve_ecrite' => $this->whenLoaded('concoursSession', fn () => $this->concoursSession ? (bool) $this->concoursSession->avec_epreuve_ecrite : null),
            'moyenne_concours' => $this->moyenneConcours(),
            'annee_scolaire_id' => $this->annee_scolaire_id,
            'etudiant_id' => $this->etudiant_id,
            'niveau' => new NiveauResource($this->resource->niveau),
            'filiere' => new FiliereResource($this->resource->filiere),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'acceptation_date' => $this->acceptation_date,
            'end_accessibility_date' => $this->end_accessibility_date,
            'album' => new AlbumResource($this->resource->album),
            'promotion' => $this->promotion,
            'advertiser_id' => $this->advertiser_id,
            'advertiser' => new AdvertiserResource($this->advertiser),
        ];
    }

    private function computeStatut(): string
    {
        if ($this->rectification_expected) {
            return 'rectification_demandee';
        }
        if ($this->motif) {
            return 'rejete';
        }
        if (!$this->dossier_valide) {
            return 'en_etude';
        }
        if (!$this->frais_paye) {
            return 'en_attente_paiement';
        }
        if (!$this->participation) {
            return 'en_attente_participation';
        }
        if (!$this->admission) {
            return 'en_attente_admission';
        }
        return 'admis';
    }
}
