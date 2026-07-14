<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtudiantRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Récupérer le dernier groupe actif s'il existe
        $dernierGroup = $this->etudiantGroups->first();

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'genre' => $this->genre,
            'photo_url' => (function() {
                if ($this->relationLoaded('submittedDocuments') && $this->submittedDocuments->isNotEmpty()) {
                    $photoKeys = \Illuminate\Support\Facades\Cache::remember('photo_document_keys', 3600, function () {
                        return \App\Models\DocumentType::where('is_photo', true)->pluck('document_key')->toArray();
                    });
                    if (empty($photoKeys)) $photoKeys = ['photo', 'photo_identite'];
                    
                    $photoDoc = $this->submittedDocuments->whereIn('document_key', $photoKeys)->first();
                    if ($photoDoc && $photoDoc->file_path) {
                        return asset(\Illuminate\Support\Facades\Storage::url($photoDoc->file_path));
                    }
                }
                return $this->image ? $this->ImagePath() : null;
            })(),
            'matricule' => $this->matricule,
            'biographie' => $this->biographie,
            'annee_admission' => $this->annee_admission,
            'slug' => $this->slug,
            'tel' => $this->tel,
            'nom_jeune_fille' => $this->nom_jeune_fille,
            'date_naissance' => $this->date_naissance,
            'lieu_naissance' => $this->lieu_naissance,
            'nationalite' => $this->nationalite,

            // Relations si elles existent
            // 'groupes' => $this->etudiantGroups->map(function ($etudiantGroup) {
            //     return [
            //         'id' => $etudiantGroup->id,
            //         'group' => $etudiantGroup->group ? [
            //             'id' => $etudiantGroup->group->id,
            //             'nom' => $etudiantGroup->group->nom
            //         ] : null,
            //         'filiere' => $etudiantGroup->filiere ? [
            //             'id' => $etudiantGroup->filiere->id,
            //             'nom' => $etudiantGroup->filiere->nom
            //         ] : null,
            //         'niveau' => $etudiantGroup->niveau ? [
            //             'id' => $etudiantGroup->niveau->id,
            //             'nom' => $etudiantGroup->niveau->nom
            //         ] : null,
            //         'annee_scolaire_id' => $etudiantGroup->annee_scolaire_id,
            //     ];
            // }),

            // Optionnel : le dernier groupe actif directement
            'dernier_groupe' => $dernierGroup ? [
                'id' => $dernierGroup->id,
                'group' => $dernierGroup->group ? [
                    'id' => $dernierGroup->group->id,
                    'nom' => $dernierGroup->group->nom
                ] : null,
                'filiere' => $dernierGroup->filiere ? [
                    'id' => $dernierGroup->filiere->id,
                    'nom' => $dernierGroup->filiere->nom
                ] : null,
                'niveau' => $dernierGroup->niveau ? [
                    'id' => $dernierGroup->niveau->id,
                    'nom' => $dernierGroup->niveau->libelle
                ] : null,
                'mode_formation' => $dernierGroup->mode_formation,
                'annee_scolaire_id' => $dernierGroup->annee_scolaire_id,
            ] : null,
            'statut' => $this->statut,
            'promotion' => $this->promotion,
            'est_nouveau' => (int) $this->annee_admission === (int) \Carbon\Carbon::parse(\App\Models\AnneeScolaire::where('active', true)->value('date_debut'))->year,
            'advertiser' => new AdvertiserResource($this->advertiser),
            'album' => $this->relationLoaded('submittedDocuments') ? $this->submittedDocuments->pluck('file_path', 'document_key')->toArray() : null,
            'tuteur' => $this->tuteur,
            'responsable' => $this->responsable,
            'roles' => $this->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'nom' => $role->nom,
                    'slug' => $role->slug,
                ];
            }),
        ];
    }
}
