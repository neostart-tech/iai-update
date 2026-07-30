<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardPaiementService;
use Illuminate\Support\Facades\Validator;

class DashboardPaiementController extends Controller
{
     protected $dashboardService;

    public function __construct(DashboardPaiementService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Récupère les statistiques globales du dashboard
     */
    public function getStatistiques(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode' => 'nullable|in:semaine,mois,annee,personnalise',
            'date_debut' => 'required_if:periode,personnalise|nullable|date',
            'date_fin' => 'required_if:periode,personnalise|nullable|date|after_or_equal:date_debut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $statistiques = $this->dashboardService->getStatistiquesGlobales(
                $request->periode ?? 'annee',
                $request->date_debut,
                $request->date_fin
            );

            return response()->json([
                'success' => true,
                'data' => $statistiques
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques pour une filière spécifique
     */
    public function getStatistiquesFiliere($filiereId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode' => 'nullable|in:semaine,mois,annee,personnalise',
            'date_debut' => 'required_if:periode,personnalise|nullable|date',
            'date_fin' => 'required_if:periode,personnalise|nullable|date|after_or_equal:date_debut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $statistiques = $this->dashboardService->getStatistiquesFiliere(
                $filiereId,
                $request->periode ?? 'annee',
                $request->date_debut,
                $request->date_fin
            );

            return response()->json([
                'success' => true,
                'data' => $statistiques
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques pour un niveau spécifique
     */
    public function getStatistiquesNiveau($niveauId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode' => 'nullable|in:semaine,mois,annee,personnalise',
            'date_debut' => 'required_if:periode,personnalise|nullable|date',
            'date_fin' => 'required_if:periode,personnalise|nullable|date|after_or_equal:date_debut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $statistiques = $this->dashboardService->getStatistiquesNiveau(
                $niveauId,
                $request->periode ?? 'annee',
                $request->date_debut,
                $request->date_fin
            );

            return response()->json([
                'success' => true,
                'data' => $statistiques
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la liste des étudiants en retard
     */
    public function getEtudiantsEnRetard(Request $request)
    {
        $limit = $request->get('limit', 20);

        try {
            $etudiants = $this->dashboardService->getEtudiantsEnRetard($limit);

            return response()->json([
                'success' => true,
                'data' => $etudiants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère l'historique des paiements pour une période
     */
    public function getHistoriquePaiements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $historique = $this->dashboardService->getHistoriquePaiements(
                $request->date_debut,
                $request->date_fin,
                $request->get('page', 1),
                $request->get('per_page', 20)
            );

            return response()->json([
                'success' => true,
                'data' => $historique
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique: ' . $e->getMessage()
            ], 500);
        }
    }

   
}
