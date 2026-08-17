<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Services\EtudiantSituationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;


class EtudiantSituationController extends Controller
{
    protected $situationService;

    public function __construct(EtudiantSituationService $situationService)
    {
        $this->situationService = $situationService;
    }

    /**
     * Récupère la situation de tous les étudiants
     */
   public function index()
    {
        try {
            // Récupérer tous les étudiants avec leur situation
            $etudiants = $this->situationService->getSituationEtudiants();
            
            // Récupérer les données pour les filtres
            $filieres = Filiere::select('id', 'nom')->get();
            $niveaux = Niveau::select('id', 'libelle')->get();
            $anneesScolaires = AnneeScolaire::select('id', 'nom')->orderBy('nom', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'etudiants' => $etudiants,
                    'filieres' => $filieres,
                    'niveaux' => $niveaux,
                    'annees_scolaires' => $anneesScolaires
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ], 500);
        }
    }


     public function exportCSV(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'filiere_id' => 'nullable|exists:filieres,id',
                'niveau_id' => 'nullable|exists:niveaux,id',
                'statut' => 'nullable|in:solde,en_cours,en_retard,aucun_frais',
                'recherche' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer les filtres de la requête
            $filtres = $request->only(['filiere_id', 'niveau_id', 'statut', 'recherche']);
            
            // Générer le CSV
            $csvData = $this->situationService->exportCSV($filtres);
            
            // Créer le nom du fichier
            $filename = 'etudiants_situation_' . date('Y-m-d_His') . '.csv';
            
            // Créer la réponse streamée
            $response = new StreamedResponse(function() use ($csvData) {
                $handle = fopen('php://output', 'w');
                
                // Ajouter le BOM UTF-8 pour Excel
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                foreach ($csvData as $row) {
                    fputcsv($handle, $row, ';'); // Utiliser le point-virgule pour Excel
                }
                
                fclose($handle);
            });
            
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response->headers->set('Expires', '0');
            
            return $response;
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'exportation CSV: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Récupère la situation détaillée d'un étudiant spécifique
     */
    public function show($id)
    {
        try {
            $etudiantModel = \App\Models\Etudiant::where('id', $id)->orWhere('slug', $id)->first();

            if (!$etudiantModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], 404);
            }

            $etudiants = $this->situationService->getSituationEtudiants(['recherche' => $etudiantModel->id]);
            $etudiant = collect($etudiants)->firstWhere('id', $etudiantModel->id);

            if (!$etudiant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $etudiant
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques globales uniquement
     */
    public function statistiques()
    {
        try {
            $statistiques = $this->situationService->getStatistiquesGlobales();

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
     * Met à jour le statut global d'un étudiant
     */
    public function updateStatut(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|string|in:actif,bloque',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $etudiant = \App\Models\Etudiant::where('id', $id)->orWhere('slug', $id)->first();
            if (!$etudiant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Étudiant non trouvé'
                ], 404);
            }

            $etudiant->update(['statut' => $request->statut]);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data' => $etudiant
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour le statut global de plusieurs étudiants à la fois
     */
    public function bulkUpdateStatut(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:etudiants,id',
                'statut' => 'required|string|in:actif,bloque',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $count = \App\Models\Etudiant::whereIn('id', $request->ids)->update(['statut' => $request->statut]);

            return response()->json([
                'success' => true,
                'message' => $count . ' étudiant(s) mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des statuts: ' . $e->getMessage()
            ], 500);
        }
    }
}