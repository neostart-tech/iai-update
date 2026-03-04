<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Services\PaiementExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaiementExportController extends Controller
{
    protected $exportService;

    public function __construct(PaiementExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Exporte les paiements selon les critères
     */
    public function exportPaiements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:etudiant,niveau,filiere,global',
            'id' => 'required_if:type,etudiant,niveau,filiere|integer|nullable',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date|after_or_equal:periode_debut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier que l'élément existe
            if ($request->type === 'etudiant' && $request->id) {
                $etudiant = Etudiant::find($request->id);
                if (!$etudiant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Étudiant non trouvé'
                    ], 404);
                }
            }

            if ($request->type === 'niveau' && $request->id) {
                $niveau = Niveau::find($request->id);
                if (!$niveau) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Niveau non trouvé'
                    ], 404);
                }
            }

            if ($request->type === 'filiere' && $request->id) {
                $filiere = Filiere::find($request->id);
                if (!$filiere) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Filière non trouvée'
                    ], 404);
                }
            }

            return $this->exportService->export(
                $request->type,
                $request->id,
                $request->periode_debut,
                $request->periode_fin
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la liste des niveaux pour le dropdown
     */
    public function getNiveaux()
    {
        $niveaux = Niveau::orderBy('nom')->get(['id', 'nom']);
        return response()->json([
            'success' => true,
            'data' => $niveaux
        ]);
    }

    /**
     * Récupère la liste des filières pour le dropdown
     */
    public function getFilieres()
    {
        $filieres = Filiere::orderBy('nom')->get(['id', 'nom']);
        return response()->json([
            'success' => true,
            'data' => $filieres
        ]);
    }

    /**
     * NOUVELLE MÉTHODE: Récupère les données pour le PDF
     */
    public function getExportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:etudiant,niveau,filiere,global',
            'id' => 'required_if:type,etudiant,niveau,filiere|nullable|integer',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date|after_or_equal:periode_debut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $this->exportService->getExportDataForPDF(
                $request->type,
                $request->id ?? null,
                $request->periode_debut ?? null,
                $request->periode_fin ?? null
            );

            return response()->json([
                'success' => true,
                'etudiants' => $data['etudiants'],
                'totaux' => $data['totaux']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données : ' . $e->getMessage()
            ], 500);
        }
    }
}