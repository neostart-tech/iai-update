<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConcoursMatiere;
use App\Traits\ActionsTraits\AuthorizesConcoursManagementTrait;
use Illuminate\Http\Request;

class ConcoursMatiereController extends Controller
{
    use AuthorizesConcoursManagementTrait;

    public function index()
    {
        $this->authorizeConcoursManagement();

        return ConcoursMatiere::orderBy('nom')->get();
    }

    public function store(Request $request)
    {
        $this->authorizeConcoursManagement();

        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        $matiere = ConcoursMatiere::create($request->only(['nom', 'code']));

        return response()->json($matiere, 201);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeConcoursManagement();

        $matiere = ConcoursMatiere::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        $matiere->update($request->only(['nom', 'code']));

        return response()->json($matiere);
    }

    public function destroy($id)
    {
        $this->authorizeConcoursManagement();

        ConcoursMatiere::destroy($id);

        return response()->json(['message' => 'Matière supprimée avec succès']);
    }
}
