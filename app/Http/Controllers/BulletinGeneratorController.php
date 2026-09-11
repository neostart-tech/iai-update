<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\ReleveNote;
use App\Services\NoteCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BulletinGeneratorController extends Controller
{
    protected $noteService;

    public function __construct(NoteCalculationService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function generatePdf(Request $request, Etudiant $etudiant)
    {
        $request->validate([
            'releve_id' => 'required|integer|exists:releve_notes,id'
        ]);

        $releve = ReleveNote::with(['anneeScolaire', 'periode'])->findOrFail($request->releve_id);

        // Fetch the structured data
        $releveData = $this->noteService->getReleveFormatted($etudiant, $releve->anneeScolaire, $releve->periode);

        // Hardcoded standard layout
        $layout = [
            ['type' => 'header', 'options' => ['logo_align' => 'center', 'show_slogan' => true]],
            ['type' => 'info_etudiant'],
            ['type' => 'tableau_notes', 'options' => ['show_coef' => true, 'show_appreciation' => false, 'group_by_ue' => true]],
            ['type' => 'bilan', 'options' => ['show_attendance' => false]],
            ['type' => 'signatures', 'options' => ['sign_dir_etudes' => true]]
        ];
        $template = (object)['nom' => 'RELEVÉ DE NOTES'];

        $etudiantGroup = $etudiant->etudiantGroups()
            ->where('annee_scolaire_id', $releve->annee_scolaire_id)
            ->with(['filiere', 'niveau'])
            ->first();

        $filiere = $etudiantGroup ? $etudiantGroup->filiere->nom ?? 'N/A' : 'N/A';
        $niveau = $etudiantGroup ? $etudiantGroup->niveau->libelle ?? 'N/A' : null;

        $user = [
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'genre' => $etudiant->sexe ?? $etudiant->genre,
            'matricule' => $etudiant->matricule,
            'niveau' => $niveau
        ];

        // Generate PDF using DOMPDF
        $pdf = Pdf::loadView('bulletins.dynamic', [
            'releves' => $releveData,
            'user' => $user,
            'filiere' => $filiere,
            'layout' => $layout,
            'template' => $template
        ])->setPaper('A4');

        $fileName = 'Bulletin_' . $etudiant->matricule . '_' . $releve->periode->nom . '.pdf';

        return $pdf->download($fileName);
    }
}
