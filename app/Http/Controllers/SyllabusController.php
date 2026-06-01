<?php

namespace App\Http\Controllers;

use App\Models\Syllabus;
use App\Models\UniteValeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyllabusController extends Controller
{
    /**
     * Récupérer le syllabus d'une UV via son slug
     */
    public function show($uvSlug)
    {
        $uv = UniteValeur::where('slug', $uvSlug)->firstOrFail();
        $syllabus = $uv->syllabus;

        return response()->json([
            'uv' => $uv,
            'syllabus' => $syllabus
        ]);
    }

    public function store(Request $request, $uvSlug)
    {
        $uv = UniteValeur::where('slug', $uvSlug)->firstOrFail();

        $request->validate([
            'description' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'competences' => 'nullable|string',
            'plan_cours' => 'nullable|string',
            'evaluation' => 'nullable|string',
            'ressources' => 'nullable|string',
            'files' => 'nullable|array',
        ]);

        $syllabus = Syllabus::updateOrCreate(
            ['unite_valeur_id' => $uv->id],
            [
                'slug' => $uv->slug,
                'description' => $request->description,
                'objectifs' => $request->objectifs,
                'competences' => $request->competences,
                'plan_cours' => $request->plan_cours,
                'evaluation' => $request->evaluation,
                'ressources' => $request->ressources,
                'files' => $request->input('files'),
            ]
        );

        return response()->json([
            'message' => 'Syllabus enregistré avec succès',
            'syllabus' => $syllabus
        ]);
    }

    /**
     * Upload de fichiers pour le syllabus
     */
    public function uploadFile(Request $request, $uvSlug)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('syllabuses/attachments', 'public');
            
            return response()->json([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['message' => 'Aucun fichier fourni'], 400);
    }
    /**
     * Récupérer les syllabuses de l'étudiant connecté (filtrés par filière et niveau)
     */
    public function getEtudiantSyllabuses()
    {
        /** @var \App\Models\Etudiant $etudiant */
        $etudiant = auth()->user();
        
        // On récupère l'association groupe/filière/niveau de l'étudiant pour l'année en cours
        $etudiantGroup = \App\Models\EtudiantGroup::where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', injectAnneeScolaireId())
            ->first();

        if (!$etudiantGroup) {
            return response()->json(['semestres' => (object)[]]);
        }

        // Récupérer les matières (UV) filtrées par filière et niveau
        $uvs = UniteValeur::with(['syllabus', 'periode', 'niveau'])
            ->where('filiere_id', $etudiantGroup->filiere_id)
            ->where('niveau_id', $etudiantGroup->niveau_id)
            ->get();

        $semestres = [];
        foreach ($uvs as $uv) {
            $semestreNom = $uv->periode ? $uv->periode->nom : 'Semestre non défini';
            if (!isset($semestres[$semestreNom])) {
                $semestres[$semestreNom] = [];
            }
            $semestres[$semestreNom][] = $uv;
        }

        return response()->json([
            'semestres' => (object)$semestres,
            'filiere' => $etudiantGroup->filiere?->nom,
            'niveau' => $etudiantGroup->niveau?->libelle
        ]);
    }
}
