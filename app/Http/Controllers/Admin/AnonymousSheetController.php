<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationAnonymous;
use App\Models\EvaluationRoom;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnonymousSheetController extends Controller
{
    /**
     * Générer les codes anonymes pour une évaluation
     */
    public function generateCodes(Evaluation $evaluation): \Illuminate\Http\RedirectResponse
    {
        // Supprimer les anciens codes pour cette évaluation
        EvaluationAnonymous::where('evaluation_id', $evaluation->id)->delete();

        // Récupérer tous les étudiants répartis dans les salles pour cette évaluation
        $evaluationRooms = EvaluationRoom::with(['students', 'salle'])
            ->where('evaluation_id', $evaluation->id)
            ->get();

        $totalGenerated = 0;

        foreach ($evaluationRooms as $room) {
            foreach ($room->students as $student) {
                EvaluationAnonymous::create([
                    'evaluation_id' => $evaluation->id,
                    'etudiant_id' => $student->id,
                    'anonymous_code' => EvaluationAnonymous::generateUniqueCode(),
                    'salle_nom' => $room->salle->nom ?? 'Non définie'
                ]);
                $totalGenerated++;
            }
        }

        if ($totalGenerated === 0) {
            return back()->with('error', 'Aucun étudiant trouvé pour cette évaluation. Vérifiez la répartition dans les salles.');
        }

        return back()->with('success', "{$totalGenerated} codes anonymes générés avec succès.");
    }

    /**
     * Afficher la liste des codes anonymes
     */
    public function showCodes(Evaluation $evaluation)
    {
        $anonymousCodes = EvaluationAnonymous::with(['etudiant', 'evaluation'])
            ->where('evaluation_id', $evaluation->id)
            ->orderBy('salle_nom')
            ->orderBy('anonymous_code')
            ->get()
            ->groupBy('salle_nom');

        return view('admin.evaluations.anonymous-codes', [
            'evaluation' => $evaluation,
            'anonymousCodesBySalle' => $anonymousCodes
        ]);
    }

    /**
     * Générer le PDF des fiches d'anonymes pour impression
     */
    public function printSheet(Evaluation $evaluation): Response
    {
        $anonymousCodes = EvaluationAnonymous::with(['etudiant'])
            ->where('evaluation_id', $evaluation->id)
            ->orderBy('salle_nom')
            ->orderBy('anonymous_code')
            ->get()
            ->groupBy('salle_nom');

        if ($anonymousCodes->isEmpty()) {
            return abort(404, 'Aucun code anonyme trouvé. Générez d\'abord les codes.');
        }

        $pdf = Pdf::loadView('admin.evaluations.anonymous-sheet-pdf', [
            'evaluation' => $evaluation,
            'anonymousCodesBySalle' => $anonymousCodes,
            'generatedAt' => now()
        ])->setPaper('A4');

        $filename = "fiches_anonymes_{$evaluation->id}_" . date('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Exporter en CSV pour utilisation dans d'autres systèmes
     */
    public function exportCsv(Evaluation $evaluation): StreamedResponse
    {
        $filename = "codes_anonymes_{$evaluation->id}_" . date('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($evaluation) {
            $out = fopen('php://output', 'w');
            
            // UTF-8 BOM pour Excel
            fwrite($out, "\xEF\xBB\xBF");
            
            // En-têtes CSV
            fputcsv($out, [
                'Code Anonyme',
                'Matricule Étudiant',
                'Nom',
                'Prénom', 
                'Salle',
                'Évaluation',
                'Matière',
                'Date'
            ]);

            // Données
            $anonymousCodes = EvaluationAnonymous::with(['etudiant', 'evaluation.matiere'])
                ->where('evaluation_id', $evaluation->id)
                ->orderBy('salle_nom')
                ->orderBy('anonymous_code')
                ->get();

            foreach ($anonymousCodes as $code) {
                fputcsv($out, [
                    $code->anonymous_code,
                    $code->etudiant->id ?? '',
                    $code->etudiant->nom ?? '',
                    $code->etudiant->prenom ?? '',
                    $code->salle_nom,
                    $evaluation->type ?? '',
                    $evaluation->matiere->nom ?? '',
                    $evaluation->debut->format('d/m/Y H:i')
                ]);
            }
            
            fclose($out);
        }, 200, $headers);
    }

    /**
     * Supprimer tous les codes anonymes d'une évaluation
     */
    public function deleteCodes(Evaluation $evaluation): \Illuminate\Http\RedirectResponse
    {
        $deletedCount = EvaluationAnonymous::where('evaluation_id', $evaluation->id)->count();
        EvaluationAnonymous::where('evaluation_id', $evaluation->id)->delete();

        return back()->with('success', "{$deletedCount} codes anonymes supprimés.");
    }
}