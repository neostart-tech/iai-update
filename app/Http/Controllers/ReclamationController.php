<?php

namespace App\Http\Controllers;

use App\Models\{Reclamation, Note, NoteHistorique};
use App\Http\Requests\{StoreReclamationRequest, TraiterReclamationRequest};
use App\Http\Resources\NoteResource;
use App\Http\Resources\ReclamationResource;
use Illuminate\Support\Facades\{DB, Log, Storage};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReclamationController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:etudiants')->only(['store', 'mesReclamations', 'annuler']);
    //     $this->middleware('auth:sanctum')->except(['store', 'mesReclamations', 'annuler']);
    // }

    // Liste pour admin
    public function index(Request $request)
    {
        $reclamations = Reclamation::with(['etudiant', 'evaluation', 'note'])
            ->when($request->statut, fn($q, $v) => $q->where('statut', $v))
            ->latest()
            ->get();

        return ReclamationResource::collection($reclamations);
    }

      public function show(Reclamation $reclamation)
    {
        return new ReclamationResource($reclamation);
    }

    public function getCountReclamations(){
        return new ReclamationResource([
            'count' => Reclamation::where('statut', 'en_attente')->count()
        ]);
    }

  

    public function getNoteReclation(Note $note)
    {
        $etudiant = request()->user();
        $note = Note::where('etudiant_id', $etudiant->id)->where('id', $note->id)->first();


        return new NoteResource($note);
    }


    // Soumettre une réclamation (étudiant)
    public function store(StoreReclamationRequest $request, Note $note)
    {
        try {
            $etudiant = request()->user();

            if (!$etudiant->peutReclamer()) {
                return response()->json([
                    'message' => 'Vous avez trop de réclamations en cours'
                ], 403);
            }

            return DB::transaction(function () use ($request, $etudiant, $note) {


                // Upload fichier
                $fichierPath = null;
                if ($request->hasFile('fichier_justificatif')) {
                    $fichierPath = $request->file('fichier_justificatif')
                        ->store("reclamations/{$etudiant->id}", 'public');
                }

                $evaluation_id = Note::where('etudiant_id', $etudiant->id)
                    ->where('id', $note->id)->first()->getAttribute('evaluation_id');

                $exists = Reclamation::where('etudiant_id', $etudiant->id)
                    ->where('evaluation_id', $evaluation_id)
                    ->where('statut', 'en_attente')
                    ->exists();

                if ($exists) {
                    abort(403, 'Vous avez déjà une réclamation en cours pour cette évaluation.');
                }
                // Création
                $reclamation = Reclamation::create([
                    'etudiant_id' => $etudiant->id,
                    'evaluation_id' => $evaluation_id,
                    'note_id' => $note->id,
                    'motif' => $request->motif,
                    'fichier_justificatif' => $fichierPath,
                    'nouvelle_note' => $request->nouvelle_note,
                    'statut' => 'en_attente'
                ]);

                return new ReclamationResource($reclamation);
            });
        } catch (\Exception $e) {
            Log::error('Erreur réclamation: ' . $e->getMessage());
            return response()->json([$e->getMessage()], 500);
        }
    }

    // Traiter une réclamation (admin)
    public function traiter(Reclamation $reclamation, TraiterReclamationRequest $request)
    {
        try {
            // $this->authorize('traiter', $reclamation);

            return DB::transaction(function () use ($reclamation, $request) {

                $reclamation->update([
                    'statut' => $request->statut,
                    'commentaire_admin' => $request->commentaire_admin,
                    'nouvelle_note' => $request->nouvelle_note ?? $reclamation->nouvelle_note,
                    'traitee_par' => Auth::id(),
                    'traitee_le' => now()
                ]);

                // Si approuvée avec nouvelle note
                if ($request->statut === 'approuvee' && $request->nouvelle_note) {
                    $note = $reclamation->note;

                    // Historique
                    NoteHistorique::create([
                        'note_id' => $note->id,
                        'reclamation_id' => $reclamation->id,
                        'ancienne_note' => $note->note,
                        'nouvelle_note' => $request->nouvelle_note,
                        'modifiee_par' => Auth::id()
                    ]);

                    // Mise à jour note
                    $note->update(['note' => $request->nouvelle_note]);
                }

                return new ReclamationResource($reclamation);
            });
        } catch (\Exception $e) {
            Log::error('Erreur traitement: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du traitement'], 500);
        }
    }

    // Mes réclamations (étudiant)
    public function mesReclamations()
    {
        $etudiant = request()->user();

        $reclamations = $etudiant->reclamations()
            ->with(['evaluation', 'note'])
            ->latest()
            ->get();

        return ReclamationResource::collection($reclamations);
    }

    // Annuler une réclamation (étudiant)
    public function annuler(Reclamation $reclamation)
    {
        $etudiant = request()->user();

        if ($etudiant->id !== $reclamation->etudiant_id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if ($reclamation->statut !== 'en_attente') {
            return response()->json(['message' => 'Cette réclamation ne peut plus être annulée'], 400);
        }

        return DB::transaction(function () use ($reclamation) {
            if ($reclamation->fichier_justificatif) {
                Storage::disk('public')->delete($reclamation->fichier_justificatif);
            }

            $reclamation->delete();

            return response()->json(['message' => 'Réclamation annulée']);
        });
    }
}
