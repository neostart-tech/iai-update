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
            'image' => $this->image ? $this->ImagePath() : "",
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
                'annee_scolaire_id' => $dernierGroup->annee_scolaire_id,
            ] : null,
        ];
    }
}
