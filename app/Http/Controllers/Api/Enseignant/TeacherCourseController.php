<?php

namespace App\Http\Controllers\Api\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\UniteValeur;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherCourseController extends Controller
{
    /**
     * Liste des matières (UV) assignées à l'enseignant connecté
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Récupérer les UV liées à cet utilisateur via la table pivot user_unite_valeur
        // On passe par le modèle User pour récupérer ses UV
        $uvs = UniteValeur::whereHas('enseignants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['periode', 'uniteEnseignement', 'syllabus'])->get();

        return response()->json([
            'enseignant' => $user->name,
            'uvs' => $uvs
        ]);
    }
}
