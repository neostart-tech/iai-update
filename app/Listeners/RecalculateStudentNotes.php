<?php

namespace App\Listeners;

use App\Events\NoteUpdated;
use App\Events\GratificationApproved;
use App\Models\AnneeScolaire;
use App\Models\Periode;
use App\Services\NoteCalculationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecalculateStudentNotes implements ShouldQueue
{
    public function __construct(
        private NoteCalculationService $noteService
    ) {}
    
    public function handle($event)
    {
        $etudiant = $event->etudiant ?? $event->note->etudiant;
        $anneeScolaire = AnneeScolaire::courante();
        
        // Récupérer la période en cours
        $periode = Periode::where('annee_scolaire_id', $anneeScolaire->id)
            ->where('debut', '<=', now())
            ->where('fin', '>=', now())
            ->first();
            
        if ($periode) {
            $this->noteService->calculateAndSaveForStudent(
                $etudiant,
                $anneeScolaire,
                $periode
            );
        }
    }
}