<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Note;
use App\Models\Reclamation;
use App\Models\User;
use Illuminate\Http\Request;

class ReclamationController extends Controller
{
 public function store(Request $request, Note $note)
{
    $request->validate([
        'motif' => 'required|string|max:500',
        'justificatif' => 'nullable|file|max:2048',
    ]);

    $evaluation = $note->evaluation;

    if (now()->diffInDays($evaluation->created_at) > 7) {
        return response()->json(['status' => 'error', 'message' => 'La période de réclamation est dépassée.'], 403);
    }

    $existe = Reclamation::where('etudiant_id', auth()->user()->etudiant->id)
        ->where('evaluation_id', $evaluation->id)
        ->exists();

    if ($existe) {
        return response()->json(['status' => 'error', 'message' => 'Vous avez déjà réclamé cette note.'], 409);
    }

    $justificatif = null;
    if ($request->hasFile('justificatif')) {
        $justificatif = $request->file('justificatif')->store('justificatifs_reclamations', 'public');
    }

    $reclamation = Reclamation::create([
        'etudiant_id' => auth()->user()->etudiant->id,
        'evaluation_id' => $note->evaluation_id,
        'motif' => $request->motif,
        'fichier_justificatif' => $justificatif,
    ]);

    // // Notifier l’admin
    // $admin = User::where('role', 'admin')->first();
    // if ($admin) {
    //     $admin->notify(new NouvelleReclamationNotification($reclamation));
    // }

    return response()->json(['status' => 'success', 'message' => 'Réclamation envoyée avec succès.']);
}

}
