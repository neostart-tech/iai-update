<?php
// app/Http/Controllers/Api/Etudiant/MonParcoursController.php

namespace App\Http\Controllers\Api\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Services\Etudiant\ParcoursService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonParcoursController extends Controller
{
    /**
     * Récupère le parcours complet de l'étudiant connecté
     */
    public function getParcours()
    {
        try {
            // Récupérer l'étudiant connecté
            $user = Auth::user();
            
            if (!$user || !$user instanceof Etudiant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié ou non étudiant'
                ], 401);
            }

            $service = new ParcoursService($user);
            $parcours = $service->getParcoursComplet();

            return response()->json([
                'success' => true,
                'data' => $parcours
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du parcours: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les paiements pour une année spécifique
     */
    public function getPaiementsParAnnee($anneeId)
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user instanceof Etudiant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $service = new ParcoursService($user);
            $paiements = $service->getPaiementsPourAnnee($anneeId);

            return response()->json([
                'success' => true,
                'data' => $paiements
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paiements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les détails d'une année spécifique
     */
    public function getDetailsAnnee($anneeId)
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user instanceof Etudiant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $service = new ParcoursService($user);
            $frais = $service->getFraisPourAnnee($anneeId);
            $paiements = $service->getPaiementsPourAnnee($anneeId);

            return response()->json([
                'success' => true,
                'data' => [
                    'frais' => $frais,
                    'paiements' => $paiements,
                    'total_paye' => $paiements->sum('montant'),
                    'total_a_payer' => $frais['total_a_payer'] ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails: ' . $e->getMessage()
            ], 500);
        }
    }
}