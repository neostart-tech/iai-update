<?php

namespace App\Services;

use App\Models\Documentation;
use App\Models\DocumentationAccess;
use App\Models\Etudiant;

class DocumentAccessService
{
    public function getDocumentsFor($authenticatable)
    {
        $roleIds = $authenticatable->roles->pluck('id');

        $groupeIds  = collect();
        $filiereIds = collect();
        $niveauIds  = collect();

        if ($authenticatable instanceof Etudiant) {
            $etudiantGroups = $authenticatable->etudiantGroups;

            $groupeIds  = $etudiantGroups->pluck('group_id');
            $filiereIds = $etudiantGroups->pluck('filiere_id');
            $niveauIds  = $etudiantGroups->pluck('niveau_id');
        }

        return Documentation::where(function ($q) use (
            $roleIds,
            $groupeIds,
            $filiereIds,
            $niveauIds
        ) {
            $q->whereHas('accesses', function ($query) use (
                $roleIds,
                $groupeIds,
                $filiereIds,
                $niveauIds
            ) {
                $query
                    ->where(fn($q) => $q->where('access_type', DocumentationAccess::ROLE)->whereIn('access_id', $roleIds))
                    ->orWhere(fn($q) => $q->where('access_type', DocumentationAccess::GROUPE)->whereIn('access_id', $groupeIds))
                    ->orWhere(fn($q) => $q->where('access_type', DocumentationAccess::FILIERE)->whereIn('access_id', $filiereIds))
                    ->orWhere(fn($q) => $q->where('access_type', DocumentationAccess::NIVEAU)->whereIn('access_id', $niveauIds));
            })
                ->orDoesntHave('accesses'); 
        });
    }
}
