<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReclamationController extends Controller
{
    public function index(): View
    {
        $reclamations = Reclamation::query()
            ->with(['etudiant:id,matricule,nom,prenom', 'evaluation.matiere:id,nom', 'evaluation.group:id,nom'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.reclamations.index', compact('reclamations'));
    }

    public function updateStatus(Request $request, Reclamation $reclamation): RedirectResponse
    {
        $data = $request->validate([
            'statut' => ['required', 'in:en_attente,approuvee,rejetee'],
            'commentaire_admin' => ['nullable','string','max:500'],
        ]);

        $reclamation->update($data);

        successMsg('Réclamation mise à jour.');
        return back();
    }
}
