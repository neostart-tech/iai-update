<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaiementsExport;
use App\Models\AnneeScolaire;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Models\Etudiant;
use App\Models\Candidature;
use Carbon\Carbon;


class ComptabiliteController extends Controller
{
    public function dashboard(Request $request)
{
    /* ① Récupération de l’année désirée (archivage) */
    $anneeId = $request->get('year') ?: AnneeScolaire::where('active',true)->value('id');
    $annee   = AnneeScolaire::findOrFail($anneeId);
    $years   = AnneeScolaire::orderByDesc('id')->pluck('nom','id');

    /* ② Récap global */
    $totalAttendu = FraisScolarite::where('annee_scolaire_id',$anneeId)->sum('montant');
    $totalPaye    = Paiement::whereHas('tranchePaiement', fn($q)=>$q->where('annee_scolaire_id',$anneeId))
                     ->where('annule',false)
                     ->sum('montant');
    $soldes       = $this->statutCounts($anneeId,'total_paye');
    $ajours       = $this->statutCounts($anneeId,'a_jour');
    $retards      = $this->statutCounts($anneeId,'en_retard');

    /* ③ Paiements par mois (stats) */
    $mensuel = Paiement::selectRaw('MONTH(date_paiement) as m, SUM(montant) as total')
              ->whereYear('date_paiement', $annee->debut)    // ex: 2024
              ->groupBy('m')->pluck('total','m')->toArray();

    /* ④ Par tranche */
    $parTranche = TranchePaiement::where('annee_scolaire_id',$anneeId)
                  ->withSum(['paiements'=>fn($q)=>$q->where('annule',false)],'montant')
                  ->pluck('paiements_sum_montant','libelle');

    /* ⑤ Table étudiants détaillée (réutilise ta fonction d’agrégation) */
    $etudiants_infos = $this->buildEtudiantsInfos($anneeId);

    return view('comptabilite.dashboard', compact(
        'annee','years','totalAttendu','totalPaye',
        'soldes','ajours','retards','mensuel','parTranche','etudiants_infos'
    ));
}

/* Petites fonctions d’aide */
private function buildEtudiantsInfos($anneeId)
{
    /* a. on récupère l’objet AnneeScolaire – PAS de ->first() */
    $annee = AnneeScolaire::findOrFail($anneeId);

    $today = Carbon::today();

    /* b. requête étudiants + relations */
    $etudiants = Etudiant::whereHas('candidatures', fn ($q) =>
            $q->where('annee_scolaire_id', $annee->id)
        )
        ->with([
            'candidatures' => fn ($q) => $q->where('annee_scolaire_id', $annee->id)
                                           ->with([
                                               'filiere:id,nom',
                                               'niveau:id,libelle',
                                               'tranches.paiements' => fn ($q) =>
                                                   $q->where('annule', false)
                                           ]),
        ])
        ->get();

    /* c. transformation */
    $etudiants_infos = $etudiants->map(function ($etudiant) use ($today) {
        $inscription = $etudiant->candidatures->first();
        if (!$inscription) return null;

        $tranches_data = $inscription->tranches->map(function ($tranche) use ($today) {
            $paye   = $tranche->paiements->sum('montant');
            $status = $paye >= $tranche->montant
                ? 'soldé'
                : ($today->gte($tranche->date_limite) ? 'en_retard' : 'a_jour');

            return [
                'libelle'     => $tranche->libelle,
                'montant'     => $tranche->montant,
                'paye'        => $paye,
                'date_limite' => $tranche->date_limite,
                'status'      => $status,
            ];
        });

        $total_frais = $tranches_data->sum('montant');
        $total_paye  = $tranches_data->sum('paye');
        $retards     = $tranches_data->where('status', 'en_retard')->isNotEmpty();

        $statut = $total_paye >= $total_frais
            ? 'total_paye'
            : ($retards ? 'en_retard' : 'a_jour');

        return [
            'etudiant'        => $etudiant,
            'filiere'         => $inscription->filiere->nom,
            'niveau'          => $inscription->niveau->libelle,
            'total_frais'     => $total_frais,
            'total_paye'      => $total_paye,
            'statut'          => $statut,
            'tranches'        => $tranches_data->values(),
            'reste_a_payer'   => $total_frais - $total_paye,
        ];
    })->filter();        // retire les null

    /* d. IMPORTANT : on renvoie la collection ! */
    return $etudiants_infos;
}

/* ---------- 2. Méthode statutCounts inchangée ---------- */
private function statutCounts($anneeId, $statut)
{
    /* buildEtudiantsInfos() renvoie maintenant toujours une collection */
    return $this->buildEtudiantsInfos($anneeId)
                ->where('statut', $statut)
                ->count();
}
/* ---------- EXPORT CSV / EXCEL ---------- */
public function export(int $year, string $format): Response
{
    $fileName = "historique_paiements_{$year}.{$format}";
    return Excel::download(new PaiementsExport($year), $fileName);
}
}
