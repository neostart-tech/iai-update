<?php

namespace App\Http\Controllers;

use App\Models\{CahierTexte, ClassCommitteeMember, EmploiDuTemp, Etudiant};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommitteeCahierTexteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'titre' => 'required|string',
            'contenu' => 'required|string',
            'piece_jointe' => 'nullable|file|max:2048',
        ]);

        $emploi = EmploiDuTemp::with('group')->findOrFail($request->emploi_du_temps_id);

        // Simple permission check: current user must be linked to an Etudiant who is active committee member of the group
        $userId = Auth::id();
        $etudiantId = Etudiant::where('user_id', $userId)->value('id');
        if (!$etudiantId) return response()->json(['message' => 'Permission refusée'], 403);

        $isCommittee = ClassCommitteeMember::where('group_id', $emploi->group_id)
            ->where('etudiant_id', $etudiantId)
            ->where('active', true)
            ->exists();
        if (!$isCommittee) return response()->json(['message' => 'Permission refusée'], 403);

        $data = $request->only(['emploi_du_temps_id','titre','contenu']);
        if ($request->hasFile('piece_jointe')) {
            $data['piece_jointe'] = $request->file('piece_jointe')->store('cahier_textes');
        }
        $data['group_id'] = $emploi->group_id;
        $data['niveau_id'] = $emploi->group?->niveau_id;
        $data['created_by_user_id'] = $userId;
        $data['created_by_role'] = 'committee';

        CahierTexte::updateOrCreate(
            ['emploi_du_temps_id' => $data['emploi_du_temps_id']],
            $data
        );

        return response()->json(['message' => 'Cahier soumis']);
    }
}
