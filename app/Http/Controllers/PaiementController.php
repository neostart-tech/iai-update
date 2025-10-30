<?php

namespace App\Http\Controllers;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\Candidature;
use App\Models\TranchePaiement;
use App\Models\FraisScolarite;
use App\Models\AnneeScolaire;
use App\Enums\GenreEnum;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Carbon\Carbon;



class PaiementController extends Controller
{
    public function index()
{
    $paiements = Paiement::with('user')->latest()->get();
    $annee = AnneeScolaire::where('active', true)->firstOrFail();
    $etudiants = Etudiant::whereHas('candidatures', function ($q) use ($annee) {
            $q->where('annee_scolaire_id', $annee->id);
        })
        ->with([
            'candidatures' => function ($q) use ($annee) {
                $q->where('annee_scolaire_id', $annee->id)
                  ->with([
                      'filiere:id,nom',
                      'niveau:id,libelle',
                      'tranches.paiements' => function ($q) {
                          $q->where('annule', false);
                      },
                  ]);
            },
        ])
        ->get()
       
        ->filter(function ($etudiant) use ($annee) {
            $candidature = $etudiant->candidatures[0];
    

            if (!$candidature || !$candidature->niveau) {
                return false;
            }

            $frais = FraisScolarite::where('annee_scolaire_id', $annee->id)
                ->where('niveau_id', $candidature->niveau->id)
                ->first();

            if (!$frais) {
                return false;
            }

            $montantTotal =TranchePaiement::where('frais_scolarite_id', $frais->id)
                ->sum('montant');

            
            $montantPaye = Paiement::where('etudiant_id', $etudiant->id)
                ->whereHas('TranchePaiement', function ($q) use ($frais) {
                    $q->where('frais_scolarite_id', $frais->id);
                })
                ->where('annule', false)
                ->sum('montant');

            return $montantPaye < $montantTotal; 
        })
        ->values();

    return view('comptabilite.paiements._index', compact('paiements', 'etudiants'));
}


public function getTranches($etudiantId)
{
    $etudiant = Etudiant::findOrFail($etudiantId);
    $anneeActive = AnneeScolaire::where('active', true)->first();

    $candidature = Candidature::where('etudiant_id', $etudiant->id)
        ->where('annee_scolaire_id', $anneeActive->id ?? null)
        ->latest()
        ->first();

    if (!$candidature) {
        return response()->json([]);
    }

    // Récupérer l'étudiant pour connaître son genre
    $etudiant = Etudiant::findOrFail($etudiantId);
    
    // Récupérer les frais appropriés selon le genre
    $frais = FraisScolarite::with('tranchepaiement')
        ->where('niveau_id', $candidature->niveau_id)
        ->where(function ($query) use ($etudiant) {
            $query->where('genre', $etudiant->genre->value)
                  ->orWhere('genre', 'Tous');
        })
        ->orderBy('genre', 'desc') // Priorité aux frais spécifiques au genre
        ->get();

  
    $tranches = $frais->flatMap->tranchepaiement;

    
    $tranchesNonSoldees = $tranches->filter(function ($tranche) use ($etudiantId) {
        $totalPaye =Paiement::where('etudiant_id', $etudiantId)
            ->where('annule', false) 
            ->where('tranche_paiement_id', $tranche->id)
            ->sum('montant');

        return $totalPaye < $tranche->montant;
    });

    return response()->json($tranchesNonSoldees->values());
}
public function store(Request $request)
{
    $request->validate([
    'etudiant_id' => 'required|exists:etudiants,id',
    'montant' => 'required|numeric|min:0',
    'mode_paiement' => 'required|string',
    'reference' => 'nullable',
    'justificatif' => 'nullable|required_if:mode_paiement,banque,semoa|file|mimes:jpg,jpeg,png,pdf|max:2048'

], [
    'etudiant_id.required' => 'Veuillez sélectionner un étudiant',
    'montant.required' => 'Le montant est requis',
    'mode_paiement.required' => 'Le mode de paiement est requis',
    'justificatif.required_if' => 'Un justificatif est obligatoire pour un paiement par banque ou SEMOA.',
]);
 

    $etudiantId = $request->etudiant_id;
    // $trancheIdDepart = $request->tranche_paiement_id;
    $montantRestant = $request->montant;
    $mode = $request->mode_paiement;
     if($request->reference==null){
       $reference=random_int(0,99999999);
    }
    $reference = $request->reference;

    $etudiant = Etudiant::findOrFail($etudiantId);
    $anneeActive = AnneeScolaire::where('active', true)->first();

    $candidature = Candidature::where('etudiant_id', $etudiantId)
        ->where('annee_scolaire_id', $anneeActive->id ?? null)
        ->latest()
        ->first();

    if (!$candidature) {
        return redirect()->back()->with('error', 'Aucune candidature trouvée pour cet étudiant.');
    }

    // Récupérer les frais appropriés selon le genre de l'étudiant
    $frais = FraisScolarite::with('tranchepaiement')
        ->where('niveau_id', $candidature->niveau_id)
        ->where(function ($query) use ($etudiant) {
            $query->where('genre', $etudiant->genre->value)
                  ->orWhere('genre', 'Tous');
        })
        ->orderBy('genre', 'desc') // Priorité aux frais spécifiques au genre
        ->get();

    $tranches =collect($frais->flatMap->tranchepaiement);
        

        

    $recap = [];

    foreach ($tranches as $tranche) {
        $dejaPaye = Paiement::where('etudiant_id', $etudiantId)
            ->where('tranche_paiement_id', $tranche->id)
            ->sum('montant');

        $reste = $tranche->montant - $dejaPaye;

        if ($reste <= 0 || $montantRestant <= 0) continue;

        $montantAPayer = min($montantRestant, $reste);

        $path="";
        if($request->hasFile('justificatif')){
            $extension=$request->file('justificatif')->getClientOriginalExtension();
            $filename=time().'_'.$request->etudiant_id.$extension;
            $path="/justificatif_paiement"."/".$filename;
             $path=  $request->file('justificatif')->storeAs($path,$filename,'public');
        }

     


    $paiement=Paiement::create([
            'etudiant_id' => $etudiantId,
            'tranche_paiement_id' => $tranche->id,
            'montant' => $montantAPayer,
            'mode_paiement' => $mode,
            'reference' => $reference,
            'date_paiement' => now()->format('Y-m-d H:i:s'),
            'justificatif'=> $path,
        ]);

        $recap[] = [
            'tranche' => $tranche->libelle,
            'montant' => $montantAPayer,
        ];

        $paiementsAnterieurs = Paiement::where('etudiant_id', $etudiantId)
    ->orderBy('date_paiement', 'asc')
    ->get();
    $montantTotalScolarite = $tranches->sum('montant');
$montantDejaPaye = Paiement::where('etudiant_id', $etudiantId)->sum('montant');
$resteGlobal = $montantTotalScolarite - $montantDejaPaye;


   $html = view('comptabilite.paiements._recu', [
    'paiement' => $paiement,
    'etudiant' => $etudiant,
    'candidature' => $candidature,
    'recap' => $recap,
    'paiementsAnterieurs' => $paiementsAnterieurs,
     'montantTotalScolarite' => $montantTotalScolarite,
    'montantDejaPaye' => $montantDejaPaye,
    'resteGlobal' => $resteGlobal,
])->render();

$filename = 'recu_' . $paiement->id . '_' . time() . '.pdf';
$folder = storage_path('app/public/recus');

if (!file_exists($folder)) {
    mkdir($folder, 0775, true);
}

$filePath = $folder . '/' . $filename;

Browsershot::html($html)
    ->setOption('args', ['--no-sandbox']) 
    ->format('A4')
    ->margins(10, 10, 10, 10)
    ->savePdf($filePath);


$paiement->update([
    'recu' => 'storage/recus/' . $filename 
]);

        $montantRestant -= $montantAPayer;
    }



    $alerte = null;
    if ($montantRestant > 0) {
        $alerte = "Attention, le montant saisi dépasse le reste à payer. Seul le montant dû a été enregistré.";
    }

    if (empty($recap)) {
        return redirect()->back()
            ->with('error', 'Aucun paiement n\'a été enregistré. Vérifiez le montant ou les tranches.');
    }

    return redirect()->back()
        ->with('success', 'Paiement enregistré avec succès.' . ($alerte ? ' ' . $alerte : ''))
        ->with('recap', $recap);
}


public function annuler(Request $request)
{
    $request->validate([
        'paiement_id' => 'required|exists:paiements,id',
        'motif_annulation' => 'required|string',
    ]);

    $paiement = Paiement::findOrFail($request->paiement_id);

    if ($paiement->annule) {
        return back()->with('error', 'Ce paiement est déjà annulé.');
    }

    $paiement->update([
        'annule' => true,
        'motif_annulation' => $request->motif_annulation,
        'date_annulation' => now(),
        'annule_par' => auth()->id(), 
        "status"=>"annule",
    ]);

    return back()->with('success', 'Le paiement a été annulé avec succès.');
}

public function valider(Paiement $paiement)
{
    if ($paiement->status === 'valide') {
        return back()->with('error', 'Ce paiement est déjà validé.');
    } 


    $memereference = $paiement->reference;
    $paiements = Paiement::where('reference', $memereference)
        ->where('annule', false)
        ->get();
    if ($paiements->isEmpty()) {
        return back()->with('error', 'Aucun paiement trouvé avec cette référence.');
    }
    foreach ($paiements as $p) {
        if ($p->status === 'valide') {
            continue;
        }
        $p->update([
            'status' => 'valide',
        ]);
    }
  return response()->json([
        'success' => true,
        'message' => 'Le paiement a été validé avec succès.',
    ]);
    // return back()->with('success', 'Le paiement a été validé avec succès.');
}


public function getInformationPaiementEtudiants(){
$annee = AnneeScolaire::where('active', true)->firstOrFail();
    $today = Carbon::today();

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
            'etudiant'     => $etudiant,
            'filiere'      => $inscription->filiere->nom,
            'niveau'       => $inscription->niveau->libelle,
            'total_frais'  => $total_frais,
            'total_paye'   => $total_paye,
            'statut'       => $statut,
            'tranches'     => $tranches_data->values(), 
            'reste_a_payer'=> $total_frais-$total_paye,
        ];
    })->filter();

    return view('comptabilite.historique._paye', compact('annee', 'etudiants_infos'));
}



//     public function getInformationPaiementEtudiant($etudiantId){
   
// $etudiant = Etudiant::with('niveau.tranchesPaiement')->findOrFail($etudiantId);

//     $tranches = $etudiant->niveau->tranchesPaiement->map(function($tranche) use ($etudiant) {
//         $montantPaye = $etudiant->paiements()
//             ->where('annule', false)
//             ->where('tranche_paiement_id', $tranche->id)
//             ->sum('montant');

//         return [
//             'libelle' => $tranche->libelle,
//             'date_limite' => $tranche->date_limite->format('d/m/Y'),
//             'montant' => $tranche->montant,
//             'montant_paye' => $montantPaye,
//             'reste_a_payer' => $tranche->montant - $montantPaye,
//             'est_reglee' => $montantPaye >= $tranche->montant,
//         ];
//     });

//     return response()->json([
//         'tranches' => $tranches,
//         'total_du' => $tranches->sum('montant'),
//         'total_paye' => $etudiant->paiements()->where('annule', false)->sum('montant'),
//          'reste_a_payer' => $tranche->montant - $montantPaye,
//     ]);
// }
 }
