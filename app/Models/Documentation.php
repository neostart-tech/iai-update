<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function accesses()
    {
        return $this->hasMany(DocumentationAccess::class);
    }

    // Méthodes pour récupérer les différents types d'accès
    public function getRoleAccesses()
    {
        return $this->accesses->where('access_type', DocumentationAccess::ROLE);
    }

    public function getGroupeAccesses()
    {
        return $this->accesses->where('access_type', DocumentationAccess::GROUPE);
    }

    public function getFiliereAccesses()
    {
        return $this->accesses->where('access_type', DocumentationAccess::FILIERE);
    }

    public function getNiveauAccesses()
    {
        return $this->accesses->where('access_type', DocumentationAccess::NIVEAU);
    }

    // Helper pour obtenir les noms des accès
    public function getAccessNames($type)
    {
        $accesses = $this->accesses->where('access_type', $type);
        $names = [];
        
        foreach ($accesses as $access) {
            switch ($type) {
                case DocumentationAccess::ROLE:
                    $role = \App\Models\Role::find($access->access_id);
                    if ($role) $names[] = $role->nom;
                    break;
                case DocumentationAccess::GROUPE:
                    $groupe = \App\Models\Group::find($access->access_id);
                    if ($groupe) $names[] = $groupe->niveau->libelle . ' ' . $groupe->nom;
                    break;
                case DocumentationAccess::FILIERE:
                    $filiere = \App\Models\Filiere::find($access->access_id);
                    if ($filiere) $names[] = $filiere->nom;
                    break;
                case DocumentationAccess::NIVEAU:
                    $niveau = \App\Models\Niveau::find($access->access_id);
                    if ($niveau) $names[] = $niveau->libelle;
                    break;
            }
        }
        
        return $names;
    }
}