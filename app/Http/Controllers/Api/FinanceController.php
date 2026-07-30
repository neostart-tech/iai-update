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
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();

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
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
        $service = new FinanceService($anneeId);
        
        $data = $service->getSuiviRecouvrement($request->all());

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function detailRecouvrement(Request $request, $slug)
    {
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
        
        $frais = FraisEtudiant::with(['etudiant.etudiantGroups.filiere', 'etudiant.etudiantGroups.niveau', 'echeances', 'fraisScolarite.filiere', 'fraisScolarite.niveau'])
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhereHas('etudiant', function($q2) use ($slug) {
                      $q2->where('slug', $slug);
                  });
            })
            ->where('annee_scolaire_id', $anneeId)
            ->firstOrFail();
        
        $paiements = \App\Models\Paiement::where('etudiant_id', $frais->etudiant_id)
            ->where('status', 'valide')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $historique = $paiements->map(function($p) {
            return [
                'id' => $p->id,
                'date' => $p->date_paiement ?? $p->created_at->format('Y-m-d'),
                'description' => $p->nature_paiement === 'inscription' ? 'Frais d\'inscription' : ($p->payable->libelle ?? 'Tranche scolarité'),
                'reference' => 'PAI-' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                'montant' => $p->montant
            ];
        });

        $totalPayeScolarite = $paiements->filter(fn($p) => $p->nature_paiement !== 'inscription')->sum('montant');
        
        $etudiantGroup = $frais->etudiant->etudiantGroups->first();

        // Déterminer si un retard est présent
        $aUnRetard = false;
        $echeancesMapped = $frais->echeances->map(function($e) use (&$aUnRetard, $paiements) {
            $lastP = $paiements->filter(fn($p) => $p->payable_type === \App\Models\Echeance::class && $p->payable_id === $e->id)->first();
            
            $statut = $e->statut;
            if ($statut !== 'paye' && $e->date_limite < now()) {
                $statut = 'retard';
                $aUnRetard = true;
            }
            return [
                'id' => $e->id,
                'libelle' => $e->libelle,
                'date_echeance' => $e->date_limite,
                'montant' => $e->montant,
                'statut' => $statut,
                'date_paiement' => $lastP ? ($lastP->date_paiement ?? $lastP->created_at->format('Y-m-d')) : null,
                'mode_paiement' => $lastP ? (\App\Models\Paiement::MODES_PAIEMENT[$lastP->mode_paiement] ?? $lastP->mode_paiement) : null
            ];
        });

        $statutGlobal = $frais->statut;
        if ($frais->est_en_abandon) {
            $statutGlobal = 'abandon';
        } elseif ($aUnRetard && $statutGlobal !== 'solde') {
            $statutGlobal = 'en_retard';
        }

        $fraisInscriptionActif = \App\Models\FraisInscription::where('active', true)
            ->where('annee_scolaire_id', $frais->annee_scolaire_id)
            ->first();
        $montantInscr = $fraisInscriptionActif ? $fraisInscriptionActif->montant : 0;
        $totalPayeInscription = $paiements->filter(fn($p) => $p->nature_paiement === 'inscription')->sum('montant');
        $statutInscr = ($totalPayeInscription >= $montantInscr && $montantInscr > 0) ? 'solde' : 'non_paye';

        return response()->json([
            'success' => true,
            'data' => [
                'etudiant' => [
                    'id' => $frais->etudiant->id,
                    'slug' => $frais->etudiant->slug,
                    'frais_id' => $frais->id,
                    'frais_slug' => $frais->slug,
                    'nom_complet' => $frais->etudiant->nom_complet ?? ($frais->etudiant->nom . ' ' . $frais->etudiant->prenom),
                    'matricule' => $frais->etudiant->matricule,
                    'statut' => $statutGlobal,
                    'filiere' => $frais->fraisScolarite->filiere->nom ?? ($etudiantGroup->filiere->nom ?? 'Non définie'),
                    'niveau' => $frais->fraisScolarite->niveau->libelle ?? ($etudiantGroup->niveau->libelle ?? 'Non défini'),
                    'derniere_activite' => $historique->first()['date'] ?? 'Aucun paiement',
                    'montant_du' => $frais->montant_apres_bourse,
                    'montant_paye' => $totalPayeScolarite,
                    'reste' => max(0, $frais->montant_apres_bourse - $totalPayeScolarite),
                    'en_retard' => $aUnRetard,
                    'inscription_statut' => $statutInscr,
                    'montant_inscription_du' => $montantInscr,
                    'montant_inscription_paye' => $totalPayeInscription,
                    'anomalie' => (new \App\Services\DiagnosticFinancierService())->verifierAnomalieEtudiant($frais->etudiant, $anneeId),
                ],
                'echeances' => $echeancesMapped,
                'historique' => $historique
            ]
        ]);
    }

    public function recouvrementJournalier(Request $request)
    {
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
        $dateFin = $request->get('date_fin', date('Y-m-d'));
        $service = new FinanceService($anneeId);

        $data = $service->getRecouvrementJournalierParNiveau($dateFin);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function suiviMensuel(Request $request)
    {
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
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
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
        
        $frais = \App\Models\FraisEtudiant::with(['etudiant', 'echeances'])
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhereHas('etudiant', function($q2) use ($slug) {
                      $q2->where('slug', $slug);
                  });
            })
            ->where('annee_scolaire_id', $anneeId)
            ->firstOrFail();

        $etudiant = $frais->etudiant;

        // Sécurité : ne pas envoyer si le compte est soldé ou abandonné
        if (in_array($frais->statut, ['solde', 'abandon'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Opération impossible : La scolarité de cet étudiant est déjà ' . $frais->statut . '.'
            ], 400);
        }

        if ($etudiant->email) {
            // Identifier les frais d'inscription impayés
            $fraisInscriptionActif = \App\Models\FraisInscription::where('active', true)
                ->where('annee_scolaire_id', $frais->annee_scolaire_id)
                ->first();
            $montantInscr = $fraisInscriptionActif ? $fraisInscriptionActif->montant : 0;
            $payeInscr = \App\Models\Paiement::where('etudiant_id', $etudiant->id)
                ->where('status', 'valide')
                ->where('nature_paiement', 'inscription')
                ->sum('montant');
            
            $inscriptionImpayee = ($payeInscr < $montantInscr && $montantInscr > 0);

            // Identifier les échéances scolarité en retard
            $retards = $frais->echeances->filter(function($e) {
                return $e->statut !== 'paye' && $e->date_limite < now();
            });

            if ($retards->isEmpty() && !$inscriptionImpayee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet étudiant n\'a aucune échéance en retard ni frais d\'inscription impayés.'
                ], 400);
            }

            $cumulDu = $retards->sum(fn($e) => $e->montant - $e->montant_paye);
            if ($inscriptionImpayee) {
                $cumulDu += ($montantInscr - $payeInscr);
            }
            
            $tranchesHtml = "<div style='background-color: #f8f9fa; border-left: 4px solid #80BF2E; padding: 15px; margin: 20px 0;'>";
            $tranchesHtml .= "<p style='margin-top: 0; font-weight: bold; color: #333;'>Détail des impayés :</p><ul style='padding-left: 20px; margin-bottom: 0;'>";
            
            if ($inscriptionImpayee) {
                $resteInscr = $montantInscr - $payeInscr;
                $tranchesHtml .= "<li style='margin-bottom: 5px;'><b>Frais d'inscription</b> : <span style='color: #dc3545;'>" . number_format($resteInscr, 0, ',', ' ') . " FCFA</span> (échue au début de l'année)</li>";
            }

            foreach ($retards as $retard) {
                $reste = $retard->montant - $retard->montant_paye;
                $tranchesHtml .= "<li style='margin-bottom: 5px;'><b>{$retard->libelle}</b> : <span style='color: #dc3545;'>" . number_format($reste, 0, ',', ' ') . " FCFA</span> (échue le {$retard->date_limite->format('d/m/Y')})</li>";
            }
            $tranchesHtml .= "</ul></div>";

            $mailContent = "
                <p style='font-size: 16px; color: #333;'>Bonjour <b>{$etudiant->prenom}</b>,</p>
                <p>Sauf erreur ou omission de notre part, nous constatons que votre compte présente un retard de paiement pour les éléments suivants aux titres des frais académiques de l'année en cours :</p>
                {$tranchesHtml}
                <p style='font-size: 18px;'>Le montant total cumulé en retard à ce jour s'élève à : <b style='color: #80BF2E;'>" . number_format($cumulDu, 0, ',', ' ') . " FCFA</b>.</p>
                <p>Nous vous prions de bien vouloir régulariser cette situation dans les plus brefs délais afin d'éviter toute suspension de vos accès ou de vos services académiques.</p>
                <p>Si vous avez effectué un règlement ces dernières 24 heures, merci de ne pas tenir compte de ce message.</p>
            ";

            $moreInfo = "Pour toute contestation ou information complémentaire, merci de vous rapprocher de la direction financière munis de vos justificatifs de paiement.";

            \Illuminate\Support\Facades\Mail::to($etudiant->email)
                ->send(new \App\Mail\RelanceTrancheMail(
                    etudiant: $etudiant, 
                    mailContent: $mailContent,
                    moreInfo: $moreInfo,
                    mailTitle: "Avis de retard de paiement - " . \App\Helpers\ConfigHelper::getAppName()
                ));

            return response()->json([
                'success' => true, 
                'message' => 'Rappel détaillé envoyé avec succès à ' . $etudiant->email
            ]);
        }
        
        return response()->json([
            'success' => false, 
            'message' => 'L\'étudiant n\'a pas d\'adresse email enregistrée.'
        ], 400);
    }

    public function declarerAbandonUI(Request $request, $slug)
    {
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
        
        $frais = \App\Models\FraisEtudiant::with('etudiant')
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhereHas('etudiant', function($q2) use ($slug) {
                      $q2->where('slug', $slug);
                  });
            })
            ->where('annee_scolaire_id', $anneeId)
            ->firstOrFail();

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
        $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
        
        $abandons = FraisEtudiant::with([
                'etudiant',
                'etudiant.etudiantGroups.niveau',
                'etudiant.etudiantGroups.filiere',
                'fraisScolarite.niveau',
                'fraisScolarite.filiere',
                'echeances'
            ])
            ->where('annee_scolaire_id', $anneeId)
            ->where('est_en_abandon', true)
            ->get()
            ->map(function ($f) {
                $etudiant    = $f->etudiant;
                $group       = $etudiant?->etudiantGroups?->first();
                $niveau      = $f->fraisScolarite?->niveau?->libelle
                            ?? $group?->niveau?->libelle
                            ?? 'Non spécifié';
                $filiere     = $f->fraisScolarite?->filiere?->nom
                            ?? $group?->filiere?->nom
                            ?? '--';

                $payeTotal   = \App\Models\Paiement::where('etudiant_id', $f->etudiant_id)
                                ->where('status', 'valide')->sum('montant');

                return [
                    'id'           => $f->id,
                    'slug'         => $f->slug,
                    'nom'          => $etudiant?->nom_complet ?? ($etudiant?->nom . ' ' . $etudiant?->prenom),
                    'matricule'    => $etudiant?->matricule,
                    'niveau'       => $niveau,
                    'filiere'      => $filiere,
                    'date_abandon' => $f->date_abandon,
                    'montant_du'   => $f->montant_apres_bourse,
                    'montant_paye' => $payeTotal,
                    'reste'        => max(0, $f->montant_apres_bourse - $payeTotal),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $abandons
        ]);
    }

    /**
     * Export Excel/CSV de la liste des etats de paiement
     */
    public function exportRecouvrement(Request $request, string $format)
    {
        $niveauId  = $request->get('niveau_id');
        $filiereId = $request->get('filiere_id');

        if ($niveauId) {
            $export = new \App\Exports\PaiementExport('niveau', $niveauId);
        } elseif ($filiereId) {
            $export = new \App\Exports\PaiementExport('filiere', $filiereId);
        } else {
            $export = new \App\Exports\PaiementExport('global');
        }

        $fileName = 'etat_paiements_' . now()->format('Ymd_Hi') . '.' . $format;
        return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
    }
}
