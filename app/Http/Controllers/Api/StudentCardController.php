<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use Illuminate\Http\Request;

class StudentCardController extends Controller
{
    /**
     * Liste les étudiants avec filtres
     */
    public function index(Request $request)
    {
        if (!auth()->user()->canGenerateStudentCards()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $query = Etudiant::with(['group.filiere', 'group.niveau']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('matricule', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filiere_id) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('filiere_id', $request->filiere_id);
            });
        }

        if ($request->niveau_id) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('niveau_id', $request->niveau_id);
            });
        }

        $etudiants = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($etudiants);
    }

    /**
     * Récupère les données complètes pour les cartes sélectionnées
     */
    public function getSelected(Request $request)
    {
        if (!auth()->user()->canGenerateStudentCards()) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $request->validate(['ids' => 'required|array']);
        
        $etudiants = Etudiant::with(['group.filiere', 'group.niveau'])
            ->whereIn('id', $request->ids)
            ->get()
            ->map(function($e) {
                return [
                    'id' => $e->id,
                    'nom' => $e->nom,
                    'prenom' => $e->prenom,
                    'nom_complet' => $e->nom . ' ' . $e->prenom,
                    'matricule' => $e->matricule,
                    'filiere' => $e->group->filiere->nom ?? 'N/A',
                    'niveau' => $e->group->niveau->libelle ?? 'N/A',
                    'image_url' => $e->ImagePath(),
                    'promotion' => $e->promotion ?? date('Y'),
                    'qr_data' => $e->matricule 
                ];
            });

        return response()->json($etudiants);
    }

    /**
     * Récupère les filtres (filières et niveaux)
     */
    public function getFilters()
    {
        return response()->json([
            'filieres' => Filiere::all(['id', 'nom']),
            'niveaux' => Niveau::all(['id', 'libelle'])
        ]);
    }
}
