<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConcoursSession;
use App\Traits\ActionsTraits\AuthorizesConcoursManagementTrait;
use Illuminate\Http\Request;

class ConcoursSessionController extends Controller
{
    use AuthorizesConcoursManagementTrait;

    public function index()
    {
        $this->authorizeConcoursManagement();

        return ConcoursSession::with('anneeScolaire')->orderByDesc('id')->get();
    }

    public function store(Request $request)
    {
        $this->authorizeConcoursManagement();

        $data = $request->validate([
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id|unique:concours_sessions,annee_scolaire_id',
            'libelle' => 'required|string|max:255',
            'avec_epreuve_ecrite' => 'boolean',
            'date_debut_depot' => 'nullable|date',
            'date_fin_depot' => 'nullable|date|after_or_equal:date_debut_depot',
            'date_epreuve' => 'nullable|date|after_or_equal:date_fin_depot',
            'date_publication_resultats' => 'nullable|date|after_or_equal:date_epreuve',
            'communique' => 'nullable|string|max:5000',
        ]);

        $data['communique'] = isset($data['communique']) ? strip_tags($data['communique']) : null;

        $session = ConcoursSession::create($data);

        return response()->json($session->load('anneeScolaire'), 201);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeConcoursManagement();

        $session = ConcoursSession::findOrFail($id);

        $data = $request->validate([
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id|unique:concours_sessions,annee_scolaire_id,' . $session->id,
            'libelle' => 'required|string|max:255',
            'avec_epreuve_ecrite' => 'boolean',
            'date_debut_depot' => 'nullable|date',
            'date_fin_depot' => 'nullable|date|after_or_equal:date_debut_depot',
            'date_epreuve' => 'nullable|date|after_or_equal:date_fin_depot',
            'date_publication_resultats' => 'nullable|date|after_or_equal:date_epreuve',
            'communique' => 'nullable|string|max:5000',
        ]);

        $data['communique'] = isset($data['communique']) ? strip_tags($data['communique']) : null;

        $session->update($data);

        return response()->json($session->load('anneeScolaire'));
    }

    public function toggleStatus(Request $request, $id)
    {
        $this->authorizeConcoursManagement();

        $session = ConcoursSession::findOrFail($id);

        $request->validate([
            'statut' => 'required|in:brouillon,ouvert,clos',
        ]);

        $session->update(['statut' => $request->input('statut')]);

        return response()->json($session);
    }

    public function publish($id)
    {
        $this->authorizeConcoursManagement();

        $session = ConcoursSession::findOrFail($id);
        $session->update(['est_publiee' => true]);

        return response()->json($session);
    }

    public function unpublish($id)
    {
        $this->authorizeConcoursManagement();

        $session = ConcoursSession::findOrFail($id);
        $session->update(['est_publiee' => false]);

        return response()->json($session);
    }
}
