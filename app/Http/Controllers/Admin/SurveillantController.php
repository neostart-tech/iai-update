<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveillantController extends Controller
{
    public function index(): View
    {
        $surveillants = User::surveillants()->with('roles')->get();
        $surveillantsInternes = User::surveillantsInternes()->count();
        $surveillantsExternes = User::surveillantsExternes()->count();

        return view('admin.surveillants.index', compact('surveillants', 'surveillantsInternes', 'surveillantsExternes'));
    }

    public function show(User $user): View
    {
        if (!$user->isSurveillant()) {
            warningMsg('Cet utilisateur n\'est pas un surveillant.');
            return redirect()->route('admin.surveillants.index');
        }

        // Récupérer les évaluations où cet utilisateur a été surveillant
        $evaluations = $user->emploiDuTemps()
            ->where('type_programme', 'evaluation')
            ->with(['evaluation', 'salle'])
            ->orderBy('debut', 'desc')
            ->paginate(20);

        return view('admin.surveillants.show', compact('user', 'evaluations'));
    }

    public function updateType(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'supervisor_type' => ['required', 'in:interne,externe,non_surveillant'],
            'supervisor_notes' => ['nullable', 'string']
        ]);

        $user->update([
            'supervisor_type' => $request->supervisor_type,
            'supervisor_notes' => $request->supervisor_notes
        ]);

        $message = match($request->supervisor_type) {
            'interne' => 'Utilisateur configuré comme surveillant interne',
            'externe' => 'Utilisateur configuré comme surveillant externe',
            'non_surveillant' => 'Utilisateur retiré de la surveillance'
        };

        successMsg($message);
        return back();
    }

    public function makeInterne(User $user): RedirectResponse
    {
        $user->update(['supervisor_type' => 'interne']);
        successMsg("{$user->completName} est maintenant surveillant interne.");
        return back();
    }

    public function makeExterne(User $user): RedirectResponse
    {
        $user->update(['supervisor_type' => 'externe']);
        successMsg("{$user->completName} est maintenant surveillant externe.");
        return back();
    }

    public function removeSurveillance(User $user): RedirectResponse
    {
        $user->update(['supervisor_type' => 'non_surveillant', 'supervisor_notes' => null]);
        successMsg("{$user->completName} n'est plus surveillant.");
        return back();
    }
}