<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use App\Models\Periode;
use App\Services\NoteCalculationService;
use Illuminate\Http\Request;

class ReleveNoteController extends Controller
{
    public function __construct(
        private NoteCalculationService $noteService
    ) {}
    
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
    
    /**
     * Recalcule le relevé (force refresh)
     */
    public function recalculate(Request $request, Etudiant $etudiant)
    {
        $anneeScolaire = AnneeScolaire::courante();
        $periodeId = $request->get('periode_id');
        
        $periode = $periodeId 
            ? Periode::findOrFail($periodeId)
            : Periode::where('annee_scolaire_id', $anneeScolaire->id)->first();
        
        $releve = $this->noteService->calculateAndSaveForStudent(
            $etudiant,
            $anneeScolaire,
            $periode
        );
        
        return response()->json([
            'message' => 'Relevé recalculé avec succès',
            'releve' => $releve
        ]);
    }
}