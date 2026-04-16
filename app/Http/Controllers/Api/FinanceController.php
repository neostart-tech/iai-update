<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use App\Models\FraisEtudiant;
use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    protected $financeService;

    public function __construct()
    {
        $this->financeService = new FinanceService();
    }

    /**
     * Dashboard Financier Principal
     */
    public function dashboard(Request $request)
    {
        $periode = $request->get('periode', 'annee');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $anneeId = $request->get('annee_id') ?? injectAnneeScolaireId();

        // Récupérer les données complètes via le service
        $service = new FinanceService($anneeId);
        $data = $service->getFullDashboardData($periode, $dateDebut, $dateFin);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Suivi Détaillé du Recouvrement
     */
    public function suiviRecouvrement(Request $request)
    {
        $anneeId = $request->get('annee_id') ?? injectAnneeScolaireId();
        $service = new FinanceService($anneeId);
        
        $data = $service->getSuiviRecouvrement($request->all());

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function detailRecouvrement(Request $request, $slug)
    {
        $frais = FraisEtudiant::where('slug', $slug)->with(['etudiant.etudiantGroups.filiere', 'etudiant.etudiantGroups.niveau', 'echeances', 'fraisScolarite.filiere', 'fraisScolarite.niveau'])->firstOrFail();
        
        $paiements = \App\Models\Paiement::where('etudiant_id', $frais->etudiant_id)
            ->where('status', 'valide')
            ->where(function($q) { $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })
            ->orderBy('date_paiement', 'desc')
            ->get();
            
        $historique = $paiements->map(function($p) {
            return [
                'id' => $p->id,
                'date' => $p->date_paiement,
                'description' => $p->nature_paiement === 'inscription' ? 'Frais d\'inscription' : 'Tranche scolarité',
                'reference' => 'PAI-' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                'montant' => $p->montant
            ];
        });

        $totalPaye = $paiements->sum('montant');
        
        $etudiantGroup = $frais->etudiant->etudiantGroups->first();

        return response()->json([
            'success' => true,
            'data' => [
                'etudiant' => [
                    'id' => $frais->id,
                    'slug' => $frais->slug,
                    'nom_complet' => $frais->etudiant->nom_complet ?? ($frais->etudiant->nom . ' ' . $frais->etudiant->prenom),
                    'matricule' => $frais->etudiant->matricule,
                    'statut' => $frais->statut,
                    'filiere' => $frais->fraisScolarite->filiere->nom ?? ($etudiantGroup->filiere->nom ?? 'Non définie'),
                    'niveau' => $frais->fraisScolarite->niveau->libelle ?? ($etudiantGroup->niveau->libelle ?? 'Non défini'),
                    'derniere_activite' => $historique->first()['date'] ?? 'Aucun paiement',
                    'montant_du' => $frais->montant_apres_bourse,
                    'montant_paye' => $totalPaye,
                    'reste' => max(0, $frais->montant_apres_bourse - $totalPaye),
                    'en_retard' => $frais->echeances->contains(function($e) {
                        return $e->date_limite < now() && $e->montant_paye < $e->montant;
                    }),
                ],
                'echeances' => $frais->echeances->map(function($e) {
                    $statut = $e->statut;
                    if ($statut !== 'paye' && $e->date_limite < now()) {
                        $statut = 'retard';
                    }
                    return [
                        'id' => $e->id,
                        'libelle' => $e->libelle,
                        'date_echeance' => $e->date_limite,
                        'montant' => $e->montant,
                        'statut' => $statut,
                        'date_paiement' => null, // mock
                        'mode_paiement' => 'Espèces' // mock
                    ];
                }),
                'historique' => $historique
            ]
        ]);
    }

    public function recouvrementJournalier(Request $request)
    {
        $anneeId = $request->get('annee_id') ?? injectAnneeScolaireId();
        $dateFin = $request->get('date_fin', date('Y-m-d'));
        $service = new FinanceService($anneeId);

        $data = $service->getRecouvrementJournalierParNiveau($dateFin);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function suiviMensuel(Request $request)
    {
        $anneeId = $request->get('annee_id') ?? injectAnneeScolaireId();
        $moisRaw = $request->get('mois', date('m'));
        $anneeRaw = $request->get('annee', date('Y'));
        
        $service = new FinanceService($anneeId);

        // Si moisRaw est une chaîne avec des virgules
        if (is_string($moisRaw) && str_contains($moisRaw, ',')) {
            $moisArr = explode(',', $moisRaw);
            $anneesArr = is_string($anneeRaw) && str_contains($anneeRaw, ',') ? explode(',', $anneeRaw) : array_fill(0, count($moisArr), $anneeRaw);
            
            $periodes = [];
            foreach ($moisArr as $i => $m) {
                $periodes[] = [
                    'mois' => (int)$m,
                    'annee' => (int)($anneesArr[$i] ?? $anneeRaw)
                ];
            }
            
            $data = $service->getSuiviMensuelParNiveau($periodes, null);
        } else {
            $data = $service->getSuiviMensuelParNiveau($moisRaw, $anneeRaw);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function envoyerRappel(Request $request, $slug)
    {
        $frais = \App\Models\FraisEtudiant::where('slug', $slug)->with('etudiant')->firstOrFail();
        $etudiant = $frais->etudiant;

        // Sécurité : ne pas envoyer si le compte est soldé ou abandonné
        if (in_array($frais->statut, ['solde', 'abandon'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Opération impossible : La scolarité de cet étudiant est déjà ' . $frais->statut . '.'
            ], 400);
        }


        
        if ($etudiant->email) {
            $message = "Nous vous informons qu'un retard de paiement a été constaté sur votre compte pour l'année scolaire en cours. Nous vous prions de bien vouloir régulariser votre situation dans les plus brefs délais.";
            \Illuminate\Support\Facades\Mail::to($etudiant->email)
                ->send(new \App\Mail\RelanceTrancheMail($etudiant, $message));
            return response()->json([
                'success' => true, 
                'message' => 'Rappel envoyé avec succès à ' . $etudiant->email
            ]);
        }
        
        return response()->json([
            'success' => false, 
            'message' => 'L\'étudiant n\'a pas d\'adresse email enregistrée.'
        ], 400);
    }

    public function declarerAbandonUI(Request $request, $slug)
    {
        $frais = \App\Models\FraisEtudiant::where('slug', $slug)->with('etudiant')->firstOrFail();
        $etudiant = $frais->etudiant;

        $frais->annoncerAbandon(
            now(),
            $frais->montant_inscription, // on garde le montant initial
            $frais->montant_scolarite,   // on garde le montant initial
            'Déclaré en abandon via le tableau de bord (Action Rapide)'
        );

        return response()->json([
            'success' => true,
            'message' => 'L\'étudiant a été déclaré en situation d\'abandon avec succès.',
            'data' => $frais
        ]);
    }

    /**
     * Gestion des Abandons API
     */
    public function declarerAbandon(Request $request, $fraisEtudiantId)
    {
        $request->validate([
            'date_abandon' => 'required|date',
            'montant_inscription' => 'required|numeric|min:0',
            'montant_scolarite' => 'required|numeric|min:0',
        ]);

        $frais = FraisEtudiant::findOrFail($fraisEtudiantId);
        $frais->annoncerAbandon(
            $request->date_abandon,
            $request->montant_inscription,
            $request->montant_scolarite,
            $request->commentaire
        );

        return response()->json([
            'success' => true,
            'message' => 'Abandon enregistré avec succès', 
            'data' => $frais
        ]);
    }

    /**
     * Liste des Abandons pour le suivi
     */
    public function listeAbandons(Request $request)
    {
        $anneeId = $request->get('annee_id') ?? injectAnneeScolaireId();
        $abandons = FraisEtudiant::with('etudiant.etudiantGroups')
            ->where('annee_scolaire_id', $anneeId)
            ->where('est_en_abandon', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $abandons
        ]);
    }
}
