<?php

namespace App\Http\Controllers;
use App\Jobs\GenererRelevesGroupe;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Evaluation;
use App\Models\Filiere;
use App\Models\Group;
use App\Models\Note;
use App\Models\UniteValeur;
use App\Models\UniteEnseignement;
use App\Models\UVWeighting;
use App\Models\AnneeScolaire;

use App\Jobs\GenererRelevesGroupeJob;
use App\Models\Periode;
use App\Models\Releve;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Cache;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Cache as FacadesCache;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Storage;



class ReleveController extends Controller
{
    public function genererReleve($etudiant_id)
    {
        $anne = '2023-2024';
        $user = Etudiant::findOrFail($etudiant_id);

        $groupe = EtudiantGroup::with('group')->where('etudiant_id', $etudiant_id)->first();
        if (!$groupe)
            return response()->json(['message' => 'Aucun groupe trouvé pour cet étudiant'], 404);

        $evaluation = Evaluation::where('group_id', $groupe->group_id)->first();
        if (!$evaluation)
            return response()->json(['message' => 'Aucune évaluation trouvée pour ce groupe'], 404);

        $uv = UniteValeur::find($evaluation->unite_valeur_id);
        $ue = UniteEnseignement::find($uv->unite_enseignement_id);

        $filiere_id = $ue->filiere_id;
        $periode_id = $ue->periode_id;
        $periode_nom = $ue->periode->nom;

        $ues = UniteEnseignement::where('filiere_id', $filiere_id)
            ->where('periode_id', $periode_id)
            ->with([
                'uniteDeValeurs.evaluations' => function ($q) use ($groupe) {
                    $q->where('group_id', $groupe->group_id);
                },
                'uniteDeValeurs.evaluations.notes' => function ($q) use ($etudiant_id) {
                    $q->where('etudiant_id', $etudiant_id);
                }
            ])->get();

        $total_notes = $total_credits = $total_credits_valides = $total_credits_non_valides = 0;
        $releve_grouped = [];

     foreach ($ues as $ue) {
    if ($ue->uniteDeValeurs->isEmpty()) {
        continue; // Ignore cette UE si elle n'a pas de UV
    }

    $ue_moyenne_ponderee = 0;
    $sum_coefficients = 0;

    $ue_validee = true;

    foreach ($ue->uniteDeValeurs as $uv) {
        $notes_by_type = ['Devoir' => 0, 'Examen' => 0, 'Interrogation' => 0, 'TP' => 0, 'Exposé' => 0];

        foreach ($uv->evaluations as $eval) {
            $note = $eval->notes->first();
            $notes_by_type[$eval->type->value] = $note ? $note->note : 0;
        }

        // Get weights per UV/filiere (defaults: 30/10/60)
        $w = UVWeighting::where('unite_valeur_id', $uv->id)
            ->where('filiere_id', $ue->filiere_id)
            ->first();
    $wd = $w->devoir ?? 30; // Devoir
    $wi = $w->interrogation ?? 10; // Interrogation
    $we = $w->examen ?? 60; // Examen
    $wtp = $w->tp ?? 0; // TP
    $wexp = $w->expose ?? 0; // Exposé
    $sumW = ($wd + $wi + $we + $wtp + $wexp);
    if ($sumW === 0) { $wd = 30; $wi = 10; $we = 60; $wtp = 0; $wexp = 0; $sumW = 100; }

        $moyenne_uv = (
            ($notes_by_type['Devoir'] * ($wd/100)) +
            ($notes_by_type['Interrogation'] * ($wi/100)) +
            ($notes_by_type['Examen'] * ($we/100)) +
            ($notes_by_type['TP'] * ($wtp/100)) +
            ($notes_by_type['Exposé'] * ($wexp/100))
        );

        if ($moyenne_uv < 10)
            $ue_validee = false;

        $releve_grouped[$ue->nom][] = [
            'uv' => $uv->nom,
            'devoir' => number_format($notes_by_type['Devoir'], 2),
            'interrogation' => number_format($notes_by_type['Interrogation'], 2),
            'examen' => number_format($notes_by_type['Examen'], 2),
            'tp' => number_format($notes_by_type['TP'], 2),
            'expose' => number_format($notes_by_type['Exposé'], 2),
            'weights_label' => sprintf('%d/%d/%d/%d/%d', (int)$wd, (int)$wi, (int)$we, (int)$wtp, (int)$wexp),
            'moyenne_uv' => number_format($moyenne_uv, 2),
            'validation' => $moyenne_uv >= 10 ? 'Validé' : 'Non validé',
            'coefficient' => $uv->coefficient
        ];

        $ue_moyenne_ponderee += $moyenne_uv * $uv->coefficient;
        $sum_coefficients += $uv->coefficient;
    }

    $moyenne_ue = $sum_coefficients > 0 ? $ue_moyenne_ponderee / $sum_coefficients : 0;
    $releve_grouped[$ue->nom][0]['moyenne_ue'] = number_format($moyenne_ue, 2);
    $releve_grouped[$ue->nom][0]['credit'] = $ue->credit;

    if ($moyenne_ue >= 10) {
        $total_credits_valides += $ue->credit;
    } else {
        $total_credits_non_valides += $ue->credit;
    }

    $total_notes += $moyenne_ue * $ue->credit;
    $total_credits += $ue->credit;
}


        $moyenne_generale = $total_credits > 0 ? number_format($total_notes / $total_credits, 2) : 0;

        return response()->json([
            'user' => [
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'genre' => $user->genre,
            ],
            'anne' => $anne,
            'periode_nom' => $periode_nom,
            'releve_grouped' => $releve_grouped,
            'moyenne_generale' => $moyenne_generale,
            'total_credits_valides' => $total_credits_valides,
            'total_credits_non_valides' => $total_credits_non_valides,
        ]);
    }

    public function generateGroupReleves(Group $groupes_id)
    {
        $user = FacadesAuth::user();

        GenererRelevesGroupeJob::dispatch($groupes_id, $user);

        return response()->json([
            'message' => 'Relevés de groupe en cours de génération. Vous serez notifié lorsque le fichier sera prêt.',
            'status' => 'processing'
        ]);
    }


    public function generateReleveForStudent(Etudiant $etudiant)
{
    $response = $this->genererReleve($etudiant->id);

    if ($response->getStatusCode() !== 200) {
        return abort(404, 'Relevé introuvable.');
    }

    $data = $response->getData(true);

    // Récupération de l'UE pour déterminer la filière
    $ue = array_key_first($data['releve_grouped']);

    $unite = UniteEnseignement::where('nom', $ue)->first();
    $filiere = $unite?->filiere?->nom ?? 'Filière non trouvée';

    // Nom du fichier
    $fileName = 'releve_' . $etudiant->id . '_' . time() . '.pdf';
    $relativePath = 'releves/' . $fileName; 
    $storagePath = storage_path('app/public/' . $relativePath);

    // Générer le PDF
    $pdf = Pdf::loadView('releves._index', [
        'releves' => $data,
        'user' => $etudiant,
        'filiere' => $filiere
    ])->setPaper('A4');

   
    FacadesStorage::disk('public')->put($relativePath, $pdf->output());


    $annee = AnneeScolaire::where('active', true)->first();

   
    $periode =Periode::where('nom', $data['periode_nom'])->first();


    Releve::create([
        'etudiant_id' => $etudiant->id,
        'annee_scolaire_id' => $annee?->id,
        'periode_id' => $periode?->id,
        'chemin_pdf' => 'storage/' . $relativePath,
        'est_publie' => false, 
        'date_publication' => null,
    ]);

    return redirect()->back()->with('success','Relevé de note générer avec succes');
    
}


    public function checked()
    {

        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $fileName = FacadesCache::get('releve_pdf_' . $userId);

        if ($fileName && FacadesStorage::disk('local')->exists('temp/' . $fileName)) {
            return response()->json([
                'ready' => true,
                'download_url' => route('admin.releves.download', ['filename' => $fileName])
            ]);
        }

        return response()->json(['ready' => false]);
    }


    public function download($filename)
    {
        $path = storage_path('app/temp/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }



    public function showViewReleveForAuthStudent(){

        return view('etudiants.my-space.releves._releves');


    }
     public function showReleveForAuthStudent(){

        $data= $this->genererReleve(auth()->user()->id);
        return $data;


    }

}
