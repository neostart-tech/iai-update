<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use App\Models\Periode;
use App\Services\NoteCalculationService;
use Illuminate\Http\Request;

class ReleveNoteController extends Controller
{
    protected $noteService;

    public function __construct(\App\Services\NoteCalculationService $noteService)
    {
        $this->noteService = $noteService;
    }

    /**
     * Liste tous les relevés de notes avec filtres
     */
    public function index(Request $request)
    {
        $query = \App\Models\ReleveNote::with(['etudiant', 'anneeScolaire', 'periode']);

        if ($request->periode_id) {
            $query->where('periode_id', $request->periode_id);
        }

        if ($request->group_id) {
            // Filtrer par groupe via les relations de l'étudiant
            $query->whereHas('etudiant.etudiantGroups', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        $releves = $query->latest()->paginate(20);

        return response()->json($releves);
    }
    
    /**
     * Récupère le relevé de notes d'un étudiant
     */
    public function show(Request $request, Etudiant $etudiant)
    {
        $anneeScolaire = AnneeScolaire::courante();
        $periodeId = $request->get('periode_id');
        
        $periode = $periodeId 
            ? Periode::findOrFail($periodeId)
            : Periode::where('annee_scolaire_id', $anneeScolaire->id)->first();
        
        $releve = $this->noteService->getReleveFormatted(
            $etudiant,
            $anneeScolaire,
            $periode
        );
        
        return response()->json($releve);
    }

    public function showReleve(Request $request, Etudiant $etudiant)
    {
        $anneeScolaireId = $request->get('annee_scolaire_id');
        
        $releves = $this->noteService->getRelevesByYear($etudiant, $anneeScolaireId);
        
        return response()->json([
            'success' => true,
            'data' => $releves,
            'filters' => [
                'annee_scolaire_id' => $anneeScolaireId
            ]
        ]);
    }
    
    /**
     * Recalcule le relevé (force refresh)
     */
    public function recalculate(Request $request, Etudiant $etudiant)
    {
        $periodeId = $request->get('periode_id');
        $periode = Periode::findOrFail($periodeId);
        $anneeScolaire = $periode->anneeScolaire;
        
        $releve = $this->noteService->calculateAndSaveForStudent(
            $etudiant,
            $anneeScolaire,
            $periode
        );

        // Charger les relations pour le formatage
        $releve->load(['anneeScolaire', 'periode', 'ueValidations.uniteEnseignement', 'uvValidations.uniteValeur']);
        
        // Formater le relevé pour le retour
        $formattedReleve = $this->noteService->getReleveFormatted($etudiant, $anneeScolaire, $periode);
        
        return response()->json([
            'message' => 'Relevé recalculé avec succès',
            'releve' => $formattedReleve
        ]);
    }

    /**
     * Supprime un relevé de notes et ses validations associées
     */
    public function destroy(\App\Models\ReleveNote $releve)
    {
        try {
            \DB::beginTransaction();

            // Supprimer les validations associées
            $releve->ueValidations()->delete();
            $releve->uvValidations()->delete();
            
            // Supprimer le relevé lui-même
            $releve->delete();

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Relevé supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génération en masse des relevés de notes
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:etudiants,id',
            'periode_id' => 'required|exists:periodes,id',
        ]);

        $periode = Periode::findOrFail($request->periode_id);
        $anneeScolaire = $periode->anneeScolaire;
        $studentIds = $request->student_ids;

        $results = [
            'success' => 0,
            'errors' => 0,
            'messages' => []
        ];

        foreach ($studentIds as $studentId) {
            try {
                $etudiant = Etudiant::findOrFail($studentId);
                
                // Calculer et sauvegarder
                $this->noteService->calculateAndSaveForStudent(
                    $etudiant,
                    $anneeScolaire,
                    $periode
                );
                
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors']++;
                $results['messages'][] = "Erreur pour l'étudiant ID $studentId : " . $e->getMessage();
            }
        }

		return response()->json([
			'success' => true,
			'message' => "Génération terminée : {$results['success']} succès, {$results['errors']} erreurs.",
			'results' => $results,
			'updated_statuses' => $this->getRelevesStatus($request)
		]);
	}

	public function getRelevesStatus(Request $request)
	{
		$releves = \App\Models\ReleveNote::whereIn('etudiant_id', $request->student_ids)
			->where('periode_id', $request->periode_id)
			->with(['etudiant', 'anneeScolaire', 'periode'])
			->get();

		$status = [];
		foreach ($releves as $releve) {
			$status[$releve->etudiant_id] = [
				'exists' => true,
				'releve_id' => $releve->id,
				'date' => $releve->created_at->format('d/m/Y H:i'),
				'data' => $this->noteService->getReleveFormatted(
					$releve->etudiant,
					$releve->anneeScolaire,
					$releve->periode
				)
			];
		}

		return $status;
	}

	public function checkStatuses(Request $request)
	{
		return response()->json([
			'statuses' => $this->getRelevesStatus($request)
		]);
	}
}