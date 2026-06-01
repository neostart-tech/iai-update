<?php

namespace App\Http\Controllers\Api\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Periode;
use App\Models\UniteValeur;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    /**
     * Liste des matières de l'étudiant groupées par semestre
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Si l'utilisateur n'est pas un étudiant, on essaie de le trouver
        if (!($user instanceof Etudiant)) {
            $user = Etudiant::find($user->id);
        }

        if (!$user) {
            return response()->json(['error' => 'Profil étudiant non trouvé'], 404);
        }

        // Récupérer le groupe actuel de l'étudiant
        $group = $user->etudiantGroups()->first();
        if (!$group) {
            return response()->json(['error' => 'Aucun groupe (classe) trouvé pour cet étudiant'], 404);
        }

        // Récupérer les UV de la filière
        $uvs = UniteValeur::where('filiere_id', $group->filiere_id)
            ->with(['periode', 'uniteEnseignement', 'syllabus'])
            ->get();

        // Grouper par période (Semestre)
        $grouped = $uvs->groupBy(function ($uv) {
            return $uv->periode ? $uv->periode->nom : 'Autres';
        });

        return response()->json([
            'etudiant' => $user->nom . ' ' . $user->prenom,
            'filiere' => $group->filiere?->nom,
            'semestres' => $grouped
        ]);
    }
}
