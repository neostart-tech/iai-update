<?php
// app/Http/Controllers/EnseignantPresenceExportController.php

namespace App\Http\Controllers;

use App\Exports\EnseignantPresencesExport;
use App\Models\EnseignantPresence;
use App\Models\EmploiDuTemp;
use App\Models\User;
use App\Services\EnseignantPresenceService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EnseignantPresenceExportController extends Controller
{
    protected $presenceService;

    public function __construct(EnseignantPresenceService $presenceService)
    {
        $this->presenceService = $presenceService;
    }

    /**
     * Exporter les présences d'un enseignant pour un cours
     */
    public function exportForCours($emploiDuTempsId)
    {
        try {
            // Récupérer le cours
            $emploi = EmploiDuTemp::with(['uv', 'group'])->find($emploiDuTempsId);
            
            if (!$emploi) {
                return response()->json(['message' => 'Cours non trouvé'], 404);
            }

            // Récupérer l'enseignant
            $enseignant = User::find($emploi->owner_id);
            
            if (!$enseignant) {
                return response()->json(['message' => 'Enseignant non trouvé'], 404);
            }

            // Récupérer les présences pour ce cours
            $presences = EnseignantPresence::with('emploiDuTemps')
                ->where('emploi_du_temps_id', $emploiDuTempsId)
                ->where('est_termine', true)
                ->orderBy('date_cours', 'desc')
                ->get();

            if ($presences->isEmpty()) {
                return response()->json(['message' => 'Aucune présence à exporter'], 404);
            }

            // Générer le nom du fichier
            $nomFichier = 'presences_enseignant_' 
                . ($enseignant->nom ?? 'inconnu') . '_'
                . ($emploi->uv->nom ?? 'cours') . '_'
                . Carbon::now()->format('Y-m-d_His') 
                . '.xlsx';

            // Exporter
            return Excel::download(
                new EnseignantPresencesExport($presences, $enseignant, $emploi),
                $nomFichier
            );

        } catch (\Exception $e) {
            \Log::error('Erreur export enseignant: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter avec filtres
     */
    public function exportFiltered(Request $request)
    {
        try {
            $request->validate([
                'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'statut' => 'nullable|in:present,absent,retard,justifie',
            ]);

            $emploiDuTempsId = $request->emploi_du_temps_id;
            
            // Récupérer le cours et l'enseignant
            $emploi = EmploiDuTemp::with(['uv', 'group'])->find($emploiDuTempsId);
            $enseignant = User::find($emploi->owner_id);

            // Construire la requête
            $query = EnseignantPresence::with('emploiDuTemps')
                ->where('emploi_du_temps_id', $emploiDuTempsId)
                ->where('est_termine', true);

            // Appliquer les filtres
            if ($request->filled('date_debut')) {
                $query->whereDate('date_cours', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->whereDate('date_cours', '<=', $request->date_fin);
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            $presences = $query->orderBy('date_cours', 'desc')->get();

            if ($presences->isEmpty()) {
                return response()->json(['message' => 'Aucune présence ne correspond aux filtres'], 404);
            }

            // Nom du fichier avec filtres
            $nomFichier = 'presences_enseignant_filtre_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new EnseignantPresencesExport($presences, $enseignant, $emploi, $request->all()),
                $nomFichier
            );

        } catch (\Exception $e) {
            \Log::error('Erreur export filtré enseignant: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter le récapitulatif par UV
     */
    public function exportRecapUV($enseignantId, $uvId)
    {
        try {
            $enseignant = User::find($enseignantId);
            $uv = \App\Models\UniteValeur::find($uvId);

            if (!$enseignant || !$uv) {
                return response()->json(['message' => 'Données non trouvées'], 404);
            }

            // Récupérer toutes les présences pour cette UV
            $presences = EnseignantPresence::with('emploiDuTemps')
                ->where('enseignant_id', $enseignantId)
                ->where('est_termine', true)
                ->whereHas('emploiDuTemps', function($q) use ($uvId) {
                    $q->where('uv_id', $uvId);
                })
                ->orderBy('date_cours', 'desc')
                ->get();

            if ($presences->isEmpty()) {
                return response()->json(['message' => 'Aucune présence pour cette UV'], 404);
            }

            // Créer un objet factice pour l'emploi du temps
            $emploiFactice = (object) [
                'uv' => $uv,
                'group' => null,
                'debut' => null,
                'fin' => null,
                'recurrence_type' => null,
                'recurrence_days' => null,
            ];

            $nomFichier = 'recap_uv_' . ($uv->code ?? $uv->nom) . '_' . Carbon::now()->format('Y-m-d') . '.xlsx';

            return Excel::download(
                new EnseignantPresencesExport($presences, $enseignant, $emploiFactice),
                $nomFichier
            );

        } catch (\Exception $e) {
            \Log::error('Erreur export recap UV: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }
}