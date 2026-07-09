<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConcoursSession;

class PublicConcoursController extends Controller
{
    /**
     * Retourne les informations publiques de la session de concours en cours
     * (dates, matières et coefficients par niveau/filière, communiqué).
     * Ne renvoie jamais de données individuelles sur les candidats.
     */
    public function index()
    {
        $session = ConcoursSession::where('est_publiee', true)
            ->with(['matieres.concoursMatiere', 'matieres.niveau', 'matieres.filiere'])
            ->latest('date_epreuve')
            ->first();

        if (!$session) {
            return response()->json([
                'data' => null,
                'message' => 'Aucune session de concours publiée pour le moment.',
            ]);
        }

        return response()->json([
            'data' => [
                'libelle' => $session->libelle,
                'avec_epreuve_ecrite' => $session->avec_epreuve_ecrite,
                'date_debut_depot' => $session->date_debut_depot,
                'date_fin_depot' => $session->date_fin_depot,
                'date_epreuve' => $session->date_epreuve,
                'date_publication_resultats' => $session->date_publication_resultats,
                'communique' => $session->communique,
                'matieres' => $session->avec_epreuve_ecrite ? $session->matieres->map(function ($sm) {
                    return [
                        'matiere' => $sm->concoursMatiere->nom,
                        'niveau' => $sm->niveau->libelle,
                        'filiere' => $sm->filiere?->nom,
                        'coefficient' => $sm->coefficient,
                    ];
                })->values() : [],
            ],
        ]);
    }
}
