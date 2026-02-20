<?php

namespace App\Http\Controllers;

use App\Models\CoursPresence;
use App\Models\EmploiDuTemp;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PresencesExport;

class PresenceExportController extends Controller
{
    /**
     * Exporter les présences d'un cours
     */
    public function exportByCours($emploiDuTempsId)
    {
        $emploi = EmploiDuTemp::with(['uv', 'group', 'salle'])->find($emploiDuTempsId);
        
        if (!$emploi) {
            return response()->json(['message' => 'Cours non trouvé'], 404);
        }

        $presences = CoursPresence::with(['etudiant'])
            ->where('emploi_du_temps_id', $emploiDuTempsId)
            ->get();

        $filename = 'presences_' . $emploi->uv->nom . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new PresencesExport($presences, $emploi), $filename);
    }

    /**
     * Exporter les présences avec filtres
     */
    public function exportWithFilters(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'statut' => 'nullable|in:present,absent,retard,justifie',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        // CORRECTION : Charger etudiant ET les relations nécessaires
        $query = CoursPresence::with([
            'etudiant', 
            'emploiDuTemps.uv', 
            'emploiDuTemps.group'
        ])->where('emploi_du_temps_id', $request->emploi_du_temps_id);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date', '<=', $request->date_fin);
        }

        $presences = $query->get();
        $emploi = EmploiDuTemp::with(['uv', 'group'])->find($request->emploi_du_temps_id);

        $filename = 'presences_' . $emploi->uv->nom . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new PresencesExport($presences, $emploi), $filename);
    }
}