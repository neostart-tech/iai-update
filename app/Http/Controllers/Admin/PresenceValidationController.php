<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoursPresence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresenceValidationController extends Controller
{
    public function index(Request $request): View
    {
        $query = CoursPresence::with(['etudiant', 'cours.group', 'cours.uv'])
            ->where('statut', 'absent')
            ->where('needs_validation', true)
            ->orderByDesc('created_at');

        // Simple search by student name or comment (optional)
        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('etudiant', function ($qs) use ($search) {
                    $qs->where('nom', 'like', "%$search%")
                       ->orWhere('prenom', 'like', "%$search%");
                })->orWhere('commentaire', 'like', "%$search%");
            });
        }

        // Filters: group, date, uv
        if ($groupId = $request->integer('group_id')) {
            $query->whereHas('cours', fn($qc)=>$qc->where('groupe_id', $groupId));
        }
        if ($date = $request->get('date')) {
            $query->whereHas('cours', fn($qc)=>$qc->whereDate('date_cours', $date));
        }
        if ($uvId = $request->integer('uv_id')) {
            $query->whereHas('cours', fn($qc)=>$qc->where('uv_id', $uvId));
        }

        $presences = $query->paginate(20)->withQueryString();

    $groups = \App\Models\Group::orderBy('nom')->get(['id','nom']);
    $uvs = \App\Models\UniteValeur::orderBy('nom')->get(['id','nom']);

        return view('admin.presences.index', compact('presences','groups','uvs'));
    }

    public function validateOne(Request $request, CoursPresence $presence): RedirectResponse
    {
        if (!$presence->needs_validation) {
            return back()->with('info', 'Déjà validé');
        }
        $presence->update([
            'needs_validation' => false,
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
        ]);
        return back()->with('success', 'Absence validée');
    }

    public function validateBatch(Request $request): RedirectResponse
    {
        $idsInput = $request->get('ids', []);
        // Support both an array of ids (ids[]=1&ids[]=2) and a comma-separated string ("1,2")
        if (is_string($idsInput)) {
            $ids = array_values(array_filter(array_map('trim', explode(',', $idsInput))));
        } else {
            $ids = (array) $idsInput;
        }
        if (empty($ids)) return back()->with('warning', 'Aucune sélection');
        CoursPresence::whereIn('id', $ids)
            ->where('needs_validation', true)
            ->update([
                'needs_validation' => false,
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
            ]);
        return back()->with('success', 'Absences validées');
    }
}
