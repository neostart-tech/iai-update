<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AnneeScolaire;
use App\Models\CahierTexte;
use App\Models\Cours;
use App\Models\CoursPresence;
use App\Models\Devoir;
use App\Models\EmploiDuTemp;
use App\Models\EnseignantPresence;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Group;
use App\Models\Evaluation;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EspaceProfesseurControleur extends Controller
{
    public function show()
    {
        return view('professeurs._index');
    }

    public function mescours()
    {
        $cours = EmploiDuTemp::with(['salle', 'group', 'uv','owner'])
            ->where('owner_id', Auth::guard('enseignants')->user()->id)
            ->get();

        $formatted = $cours->map(function ($c) {
            return [
                'title' => ($c->uv?->uniteEnseignement?->nom ?? $c->uv?->nom ?? $c->details ?? 'Cours').' - '.($c->salle->nom ?? 'Salle ?'),
                'start' => $c->debut,
                'end' => $c->fin,
                'extendedProps' => [
                    'salle' => $c->salle->nom ?? 'Salle inconnue',
                    'salle_id' => $c->salle->id ?? null,
                    'groupe_id' => $c->group->id ?? null,
                    'groupe' => $c->group->nom ?? 'Groupe inconnu',
                    'type_programme' => $c->type_programme,
                    'uv_id' => $c->uv_id,
                    'emploi_du_temps_id' => $c->id,
                    "annee_scolaire_id" => $c->annee_scolaire_id,
                    'matiere' => $c->uv?->nom ?? $c->uv?->code ?? null,
                    'creneau' => ($c->debut?->format('H:i') ?? '').' - '.($c->fin?->format('H:i') ?? ''),
                    'is_online' => $c->is_online,
                    'duration_minutes' => $c->duration_minutes ?? null,
                    'security_level' => $c->security_level ?? null,
                    'debut' => $c->debut,
                    'user'=> $c->owner?->completName() ?? $c->owner?->name ?? 'Inconnu',
                    'fin' => $c->fin,
                    'details' => $c->details,
                    'user_id' => $c->owner_id,
                    'autosave_enabled' => $c->autosave_enabled ?? null,
                    'disable_copy_paste' => $c->disable_copy_paste ?? null,
                    'disable_right_click' => $c->disable_right_click ?? null,
                    'disable_printscreen' => $c->disable_printscreen ?? null,
                    'forbid_tab_switch' => $c->forbid_tab_switch ?? null,
                    'max_focus_lost' => $c->max_focus_lost ?? null,
                    'auto_submit_on_time_end' => $c->auto_submit_on_time_end ?? null,
                ],
            ];
        });

        return response()->json($formatted);
    }

    // Backward-compatible alias for older routes
    public function myCourses()
    {
        return $this->mescours();
    }

    public function mesCoursShow()
    {

        return view('professeurs.mes-cours');
    }

    public function mesEvaluationsShow()
    {
        return view('professeurs.evaluations.mes-evaluations');
    }
   

}
