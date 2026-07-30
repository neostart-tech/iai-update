<?php

namespace App\Http\Controllers;

use App\Http\Resources\BourseEtudiantResource;
use App\Http\Resources\BourseResource;
use App\Http\Resources\EtudiantResource;
use App\Http\Resources\EtudiantRessource;
use App\Models\AnneeScolaire;
use App\Models\Bourse;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Pest\Support\Str;

class BourseController extends Controller
{
    public function index()
    {
        return BourseResource::collection(Bourse::with('etudiants')->get());
    }

    // public function getBoursesByEtudiant($etudiantId)
    // {
    //     $etudiant = Etudiant::with(['bourses' => function($query) {
    //         $query->withPivot('annee_scolaire_id', 'created_at', 'slug')
    //               ->orderBy('pivot_created_at', 'desc');
    //     }])->findOrFail($etudiantId);

    //     $bourses = $etudiant->bourses->map(function($bourse) use ($etudiant) {
    //         return [
    //             'id' => $bourse->pivot->id,
    //             'bourse_id' => $bourse->id,
    //             'etudiant_id' => $etudiant->id,
    //             'slug' => $bourse->pivot->slug,
    //             'annee_scolaire_id' => $bourse->pivot->annee_scolaire_id,
    //             'created_at' => $bourse->pivot->created_at,
    //             'bourse' => [
    //                 'id' => $bourse->id,
    //                 'nom' => $bourse->nom,
    //                 'type' => $bourse->type,
    //                 'valeur' => $bourse->valeur,
    //                 'description' => $bourse->description,
    //                 'libelle' => $bourse->libelle ?? $bourse->nom
    //             ]
    //         ];
    //     });

    //     return response()->json([
    //         'data' => $bourses
    //     ]);
    // }
    public function getBoursesByEtudiant($etudiantId)
    {
        $etudiant = Etudiant::with(['bourses' => function ($query) {
            $query->withPivot('id', 'annee_scolaire_id', 'created_at', 'slug')
                ->orderBy('pivot_created_at', 'desc');
        }])->findOrFail($etudiantId);

        $bourses = $etudiant->bourses->map(function ($bourse) {
            $bourse->pivot_data = [
                'id' => $bourse->pivot->id,
                'slug' => $bourse->pivot->slug,
                'annee_scolaire_id' => $bourse->pivot->annee_scolaire_id,
                'date_attribution' => $bourse->pivot->created_at
            ];

            return $bourse;
        });

        return BourseResource::collection($bourses);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|in:pourcentage,montant_fixe',
            'valeur' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        $bourse = Bourse::create($data);

        return new BourseResource($bourse);
    }

    public function show(Bourse $bourse)
    {
        return new BourseResource($bourse->load('etudiants'));
    }

    public function getEtudiantsBourse(Bourse $bourse)
    {
        return EtudiantRessource::collection($bourse->etudiants);
    }

    public function update(Request $request, Bourse $bourse)
    {

        if ($bourse->etudiants()->exists()) {
            return response()->json([
                'message' => "Impossible de modifier cette bourse car elle contient des étudiants liés"
            ], 403);
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|in:pourcentage,montant_fixe',
            'valeur' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        $bourse->update($data);

        return new BourseResource($bourse);
    }

    public function destroy(Bourse $bourse)
    {
        // Vérifier si la bourse a des étudiants
        if ($bourse->etudiants()->exists()) {
            return response()->json([
                'message' => "Impossible de supprimer cette bourse car elle contient des étudiants liés"
            ], 403);
        }

        $bourse->delete();

        return response()->json(['message' => 'Bourse supprimée avec succès']);
    }

    // public function affecter(Request $request)
    // {
    //     $data = $request->validate([
    //         'etudiant_id' => 'required|exists:etudiants,id',
    //         'bourse_id' => 'required|exists:bourses,id'
    //     ]);

    //     $etudiant = Etudiant::findOrFail($data['etudiant_id']);
    //     $etudiant->bourses()->attach($data['bourse_id'], [
    //         'slug' => Str::random(10),
    //         'annee_scolaire_id' => AnneeScolaire::courante()->id,
    //     ]);

    //     return response()->json([
    //         'message' => 'Bourse affectée avec succès',
    //     ]);
    // }
    public function affecter(Request $request)
    {
        $data = $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'bourse_id' => 'required|exists:bourses,id'
        ]);

        $etudiant = Etudiant::findOrFail($data['etudiant_id']);
        $anneeCourante = AnneeScolaire::courante();

        if (!$anneeCourante) {
            return response()->json([
                'message' => 'Aucune année scolaire active trouvée'
            ], 404);
        }

        // Vérifier si l'étudiant a déjà une bourse pour l'année active
        $bourseExistante = $etudiant->bourses()
            ->wherePivot('annee_scolaire_id', $anneeCourante->id)
            ->exists();

        if ($bourseExistante) {
            return response()->json([
                'message' => 'Cet étudiant a déjà une bourse attribuée pour l\'année scolaire en cours'
            ], 422);
        }

        // Vérifier si la bourse spécifiée est déjà attribuée à cet étudiant (au cas où)
        $bourseDejaAttribuee = $etudiant->bourses()
            ->wherePivot('bourse_id', $data['bourse_id'])
            ->wherePivot('annee_scolaire_id', $anneeCourante->id)
            ->exists();

        if ($bourseDejaAttribuee) {
            return response()->json([
                'message' => 'Cette bourse est déjà attribuée à cet étudiant pour l\'année en cours'
            ], 422);
        }

        // Si tout est OK, on procède à l'affectation
        try {
            $etudiant->bourses()->attach($data['bourse_id'], [
                'slug' => Str::random(10),
                'annee_scolaire_id' => $anneeCourante->id,
            ]);

            // Synchroniser le contrat financier
            $fraisEtudiant = \App\Models\FraisEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeCourante->id)
                ->first();
                
            if ($fraisEtudiant) {
                app(\App\Services\FraisEtudiantService::class)->synchroniserBourse($fraisEtudiant);
            }

            return response()->json([
                'message' => 'Bourse affectée avec succès',
                'data' => [
                    'etudiant' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'bourse_id' => $data['bourse_id'],
                    'annee_scolaire' => $anneeCourante->libelle
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'affectation de la bourse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function retirer(Request $request)
    {
        $data = $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'bourse_id' => 'required|exists:bourses,id'
        ]);

        $etudiant = Etudiant::findOrFail($data['etudiant_id']);
        $etudiant->bourses()->detach($data['bourse_id']);

        // Synchroniser le contrat financier
        $anneeCourante = AnneeScolaire::courante();
        if ($anneeCourante) {
            $fraisEtudiant = \App\Models\FraisEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeCourante->id)
                ->first();
                
            if ($fraisEtudiant) {
                app(\App\Services\FraisEtudiantService::class)->synchroniserBourse($fraisEtudiant);
            }
        }

        return response()->json([
            'message' => 'Bourse retirée avec succès',
            'data' => [
                'etudiant' => new EtudiantRessource($etudiant->load('bourses')),
                'bourse' => new BourseResource(Bourse::find($data['bourse_id']))
            ]
        ]);
    }
}
