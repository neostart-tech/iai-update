<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Evaluation, EvaluationRoom};
use App\Services\EvaluationRoomAllocator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvaluationRoomController extends Controller
{
    public function allocate(Request $request, Evaluation $evaluation, EvaluationRoomAllocator $allocator): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'room_ids' => ['required','array','min:1'],
            'room_ids.*' => ['integer','exists:salles,id'],
            'limit_per_room' => ['nullable','integer','min:1'],
        ]);

        // Reset existing allocation
        EvaluationRoom::where('evaluation_id', $evaluation->id)->delete();

        $allocator->allocate($evaluation, $data['room_ids'], $data['limit_per_room'] ?? null);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with(successMsg('Répartition automatique effectuée.'));
    }

    public function setSupervisors(Request $request, EvaluationRoom $evaluationRoom): RedirectResponse|JsonResponse
    {
        $payload = $request->validate([
            'user_ids' => ['required','array','min:1','max:3'],
            'user_ids.*' => ['integer','exists:users,id'],
        ]);

        $evaluationRoom->supervisors()->sync($payload['user_ids']);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with(successMsg('Surveillants affectés à la salle.'));
    }

    public function reset(Request $request, Evaluation $evaluation): RedirectResponse|JsonResponse
    {
        EvaluationRoom::where('evaluation_id', $evaluation->id)->delete();
        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with(successMsg('Répartition réinitialisée.'));
    }

    public function summary(Evaluation $evaluation): JsonResponse
    {
        $rooms = EvaluationRoom::with(['salle:id,nom,effectif','students:id','supervisors:id,nom,prenom'])
            ->where('evaluation_id', $evaluation->id)
            ->get()
            ->map(fn($er) => [
                'id' => $er->id,
                'salle' => $er->salle->nom,
                'capacity' => (int) $er->assigned_capacity,
                'students_count' => $er->students->count(),
                'supervisors' => $er->supervisors->map(fn($u) => $u->nom . ' ' . $u->prenom)->values(),
            ]);
        return response()->json(['rooms' => $rooms]);
    }

    public function exportCsv(Evaluation $evaluation): StreamedResponse
    {
        $filename = 'seating_plan_'.$evaluation->id.'_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($evaluation) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Salle', 'Etudiant ID', 'Nom', 'Prenom', 'Surveillants']);

            $rooms = EvaluationRoom::with(['salle:id,nom', 'students:id,nom,prenom', 'supervisors:id,nom,prenom'])
                ->where('evaluation_id', $evaluation->id)->get();

            foreach ($rooms as $er) {
                $sup = $er->supervisors->map(fn($u)=> $u->nom.' '.$u->prenom)->implode(', ');
                foreach ($er->students as $st) {
                    fputcsv($out, [$er->salle->nom, $st->id, $st->nom, $st->prenom, $sup]);
                }
                if ($er->students->isEmpty()) {
                    fputcsv($out, [$er->salle->nom, '', '', '', $sup]);
                }
            }
            fclose($out);
        }, 200, $headers);
    }
}
