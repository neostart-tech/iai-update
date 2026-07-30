<?php

namespace App\Http\Controllers;

use App\Services\DiagnosticFinancierService;
use Illuminate\Http\JsonResponse;

class DiagnosticFinancierController extends Controller
{
    protected $diagnosticService;

    public function __construct(DiagnosticFinancierService $diagnosticService)
    {
        $this->diagnosticService = $diagnosticService;
    }

    public function index(): JsonResponse
    {
        try {
            $result = $this->diagnosticService->comparerCalculs();
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function changerModeFormation(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'mode_formation' => 'required|string'
        ]);

        try {
            $anneeId = getAnneeScolaireId();
            $etudiant = \App\Models\Etudiant::findOrFail($request->etudiant_id);
            $etudiantGroup = $etudiant->etudiantGroups()->where('annee_scolaire_id', $anneeId)->first();

            if ($etudiantGroup) {
                $etudiantGroup->update([
                    'mode_formation' => $request->mode_formation
                ]);
            }

            // Synchroniser immédiatement les frais de l'étudiant
            $fraisService = new \App\Services\FraisEtudiantService();
            $fraisService->synchroniserApresModificationProfil($etudiant, $anneeId);

            return response()->json([
                'success' => true,
                'message' => "Mode de formation mis à jour en '{$request->mode_formation}' et frais recalculés avec succès.",
                'mode_formation' => $request->mode_formation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la modification : " . $e->getMessage()
            ], 500);
        }
    }
}
