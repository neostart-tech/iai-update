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
            'transmis_academie' => (bool) $this->transmis_academie,
            'transmis_academie_date' => $this->transmis_academie_date,
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
            'numero_bordereau' => $this->numero_bordereau,
            'moyen_connaissance_id' => $this->moyen_connaissance_id,
            'moyen_connaissance_precision' => $this->moyen_connaissance_precision,
            'moyen_connaissance' => $this->resource->moyenConnaissance ? ['id' => $this->resource->moyenConnaissance->id, 'libelle' => $this->resource->moyenConnaissance->libelle] : null,
            'numero_dossier_affiche' => $this->numero_dossier_affiche,
            'matricule_concours' => $this->matricule_concours,
            'concours_session_id' => $this->concours_session_id,
            'avec_epreuve_ecrite' => $this->whenLoaded('concoursSession', fn () => $this->concoursSession ? (bool) $this->concoursSession->avec_epreuve_ecrite : null),
            'moyenne_concours' => $this->moyenneConcours(),
            'annee_scolaire_id' => $this->annee_scolaire_id,
            'etudiant_id' => $this->etudiant_id,
            'etudiant' => $this->resource->etudiant ? [
                'id' => $this->resource->etudiant->id,
                'slug' => $this->resource->etudiant->slug,
                'nom' => $this->resource->etudiant->nom,
                'prenom' => $this->resource->etudiant->prenom,
                'matricule' => $this->resource->etudiant->matricule,
            ] : null,
            'niveau' => $this->resource->niveau ? new NiveauResource($this->resource->niveau) : null,
            'filiere' => $this->resource->filiere ? new FiliereResource($this->resource->filiere) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'acceptation_date' => $this->acceptation_date,
            'end_accessibility_date' => $this->end_accessibility_date,
            'album' => $this->resource->album ? new AlbumResource($this->resource->album) : null,
            'promotion' => $this->promotion,
            'advertiser_id' => $this->advertiser_id,
            'advertiser' => new AdvertiserResource($this->advertiser),
            'next_matricule' => $this->computeNextMatricule(),
            'active_annee_scolaire' => $this->computeActiveAnneeScolaire(),
            'frais_scolarite_attendu' => $this->computeFraisScolariteAttendu(),
        ];
    }

    private function computeActiveAnneeScolaire()
    {
        $activeAnnee = \App\Models\AnneeScolaire::where('active', true)->first();
        if (!$activeAnnee) return null;
        return [
            'id' => $activeAnnee->id,
            'libelle' => $activeAnnee->libelle,
            'date_debut' => $activeAnnee->date_debut
        ];
    }

    private function computeNextMatricule(): string
    {
        $activeAnnee = \App\Models\AnneeScolaire::where('active', true)->first();
        $year = $activeAnnee && $activeAnnee->date_debut ? \Carbon\Carbon::parse($activeAnnee->date_debut)->year : today()->year;
        return \App\Models\Etudiant::generateNextMatricule($year);
    }

    private function computeFraisScolariteAttendu(): float
    {
        $activeAnnee = \App\Models\AnneeScolaire::where('active', true)->first();
        if (!$activeAnnee) return 0;
        
        $fraisScolarite = \App\Models\FraisScolarite::getFraisForEtudiant(
            $this->niveau_id,
            $this->genre,
            $this->filiere_id,
            $activeAnnee->id,
            'Tous'
        );

        return $fraisScolarite ? (float) $fraisScolarite->montant : 0;
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
            return $this->transmis_academie ? 'transmis_academie' : 'en_etude';
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
