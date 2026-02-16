<?php
// app/Policies/ReclamationPolicy.php

namespace App\Policies;

use App\Models\{User, Etudiant, Reclamation};

class ReclamationPolicy
{
    public function view($user, Reclamation $reclamation): bool
    {
        if ($user instanceof Etudiant) {
            return $user->id === $reclamation->etudiant_id;
        }
        return $user instanceof User;
    }

    public function traiter($user, Reclamation $reclamation): bool
    {
        return $user instanceof User 
            && ($user->isAdmin() || $user->isResponsablePedagogique())
            && $reclamation->statut === 'en_attente';
    }
}