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
            $modeEnum = \App\Enums\ModeFormationEnum::tryFrom($request->mode_formation) ?? $request->mode_formation;

            $groups = $etudiant->etudiantGroups;
            if ($groups->count() > 0) {
                foreach ($groups as $group) {
                    $group->update([
                        'mode_formation' => $modeEnum
                    ]);
                }
            } else {
                // S'il n'y a aucun groupe pour l'étudiant, en créer un basique s'il y a un groupe dispo
                $defaultGroup = \App\Models\Group::first();
                if ($defaultGroup) {
                    $etudiant->etudiantGroups()->create([
                        'annee_scolaire_id' => $anneeId,
                        'group_id' => $defaultGroup->id,
                        'niveau_id' => $defaultGroup->niveau_id,
                        'filiere_id' => $defaultGroup->filiere_id,
                        'mode_formation' => $modeEnum
                    ]);
                }
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

    public function changerModeFormationMasse(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'etudiant_ids' => 'required|array',
            'etudiant_ids.*' => 'exists:etudiants,id',
            'mode_formation' => 'required|string'
        ]);

        try {
            $anneeId = getAnneeScolaireId();
            $fraisService = new \App\Services\FraisEtudiantService();
            $modeEnum = \App\Enums\ModeFormationEnum::tryFrom($request->mode_formation) ?? $request->mode_formation;
            $count = 0;

            foreach ($request->etudiant_ids as $etudiantId) {
                $etudiant = \App\Models\Etudiant::find($etudiantId);
                if (!$etudiant) continue;

                $groups = $etudiant->etudiantGroups;
                if ($groups->count() > 0) {
                    foreach ($groups as $group) {
                        $group->update([
                            'mode_formation' => $modeEnum
                        ]);
                    }
                }

                // Synchroniser immédiatement les frais de l'étudiant
                $fraisService->synchroniserApresModificationProfil($etudiant, $anneeId);
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "Mode de formation mis à jour en '{$request->mode_formation}' pour {$count} étudiant(s).",
                'updated_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la modification en masse : " . $e->getMessage()
            ], 500);
        }
    }
}
