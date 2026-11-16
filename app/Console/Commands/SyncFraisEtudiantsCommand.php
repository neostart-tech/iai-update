<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use App\Models\FraisScolarite;
use App\Models\FraisEtudiant;
use App\Models\Echeancier;
use App\Models\Echeance;
use App\Models\Filiere;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncFraisEtudiantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'frais:sync-etudiants 
                            {--annee= : ID de l\'année scolaire (par défaut l\'année active)} 
                            {--filiere= : Code ou nom partiel de la filière (ex: IA-GL)} 
                            {--force : Force la mise à jour même pour les étudiants ayant des échéances générées sans paiement}
                            {--dry-run : Affiche uniquement les modifications sans les enregistrer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les frais et échéances des étudiants vers les tarifs spécifiques créés pour leur filière/niveau.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $anneeId = $this->option('annee');
        if (!$anneeId) {
            $anneeActive = AnneeScolaire::where('active', true)->first();
            $anneeId = $anneeActive?->id;
        }

        if (!$anneeId) {
            $this->error('Aucune année scolaire active trouvée.');
            return 1;
        }

        $annee = AnneeScolaire::find($anneeId);
        $this->info("=== Synchronisation des frais pour l'année scolaire : " . ($annee?->nom ?? $anneeId) . " ===");

        $filiereFilter = $this->option('filiere');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('--- MODE SIMULATION (Dry-Run) : Aucune modification ne sera enregistrée ---');
        }

        // Récupérer les étudiants inscrits cette année
        $query = Etudiant::whereHas('allEtudiantGroups', function ($q) use ($anneeId) {
            $q->where('annee_scolaire_id', $anneeId);
        })->with([
            'allEtudiantGroups' => function ($q) use ($anneeId) {
                $q->where('annee_scolaire_id', $anneeId)->with(['filiere', 'niveau']);
            },
            'fraisEtudiant' => function ($q) use ($anneeId) {
                $q->where('annee_scolaire_id', $anneeId)->with(['echeances']);
            }
        ]);

        if ($filiereFilter) {
            $query->whereHas('allEtudiantGroups', function ($q) use ($anneeId, $filiereFilter) {
                $q->where('annee_scolaire_id', $anneeId)
                  ->whereHas('filiere', function ($fq) use ($filiereFilter) {
                      $fq->where('nom', 'LIKE', "%{$filiereFilter}%")
                         ->orWhere('code', 'LIKE', "%{$filiereFilter}%")
                         ->orWhere('slug', 'LIKE', "%{$filiereFilter}%");
                  });
            });
        }

        $etudiants = $query->get();
        $this->info("Nombre d'étudiants ciblés : " . $etudiants->count());

        $nbMisAJour = 0;
        $nbInchanges = 0;
        $nbIgnoresPaiement = 0;

        foreach ($etudiants as $etudiant) {
            $groupe = $etudiant->allEtudiantGroups->first();
            if (!$groupe || !$groupe->niveau_id) {
                continue;
            }

            $mode = $groupe->mode_formation instanceof \UnitEnum 
                ? $groupe->mode_formation->value 
                : ($groupe->mode_formation ?? 'Présentiel');

            // Recherche du tarif spécifique selon les critères réels
            $tarifCible = FraisScolarite::getFraisForEtudiant(
                $groupe->niveau_id,
                $etudiant->genre?->value ?? 'Tous',
                $groupe->filiere_id,
                $anneeId,
                $mode
            );

            if (!$tarifCible) {
                continue;
            }

            $fraisEtudiant = $etudiant->fraisEtudiant->first();

            // Vérifier si une mise à jour est nécessaire
            $besoinMaj = false;
            if (!$fraisEtudiant) {
                $besoinMaj = true;
            } elseif ($fraisEtudiant->frais_scolarite_id != $tarifCible->id) {
                $besoinMaj = true;
            } elseif ($fraisEtudiant->type_paiement === 'tranches_globales' && $fraisEtudiant->montant_apres_bourse != $tarifCible->montant && !$fraisEtudiant->bourse_etudiant_id) {
                $besoinMaj = true;
            }

            if (!$besoinMaj) {
                $nbInchanges++;
                continue;
            }

            // Vérifier si l'étudiant a déjà effectué des paiements
            $aPaye = false;
            if ($fraisEtudiant && $fraisEtudiant->echeances) {
                $aPaye = $fraisEtudiant->echeances->contains(function ($ech) {
                    return (float) ($ech->montant_paye ?? 0) > 0;
                });
            }

            $nomComplet = "{$etudiant->nom} {$etudiant->prenom} ({$etudiant->matricule})";
            $filiereNom = $groupe->filiere?->nom ?? 'N/A';
            $niveauNom = $groupe->niveau?->libelle ?? 'N/A';

            if ($aPaye && !$this->option('force')) {
                $this->warn("Ignoré (paiement déjà engagé) : {$nomComplet} - {$filiereNom} {$niveauNom}");
                $nbIgnoresPaiement++;
                continue;
            }

            $ancienMontant = $fraisEtudiant ? number_format($fraisEtudiant->montant_apres_bourse, 0, ',', ' ') . " FCFA" : "Aucun";
            $nouveauMontant = number_format($tarifCible->montant, 0, ',', ' ') . " FCFA";

            $this->line("<info>[MAJ]</info> {$nomComplet} ({$filiereNom} - {$niveauNom}) : {$ancienMontant} => <comment>{$nouveauMontant}</comment>");

            if (!$isDryRun) {
                DB::transaction(function () use ($etudiant, $anneeId, $tarifCible, $fraisEtudiant) {
                    // Charger les tranches du nouveau tarif
                    $tarifCible->load('tranchepaiement');
                    $montantNet = $tarifCible->montant;

                    // Gérer la bourse existante si présente
                    $bourseId = $fraisEtudiant?->bourse_etudiant_id;
                    if ($bourseId) {
                        $bourseEtudiant = \App\Models\BourseEtudiant::with('bourse')->find($bourseId);
                        if ($bourseEtudiant && $bourseEtudiant->bourse) {
                            $bourse = $bourseEtudiant->bourse;
                            if ($bourse->type === 'pourcentage') {
                                $reduction = ($tarifCible->montant * $bourse->valeur) / 100;
                                $montantNet = max(0, $tarifCible->montant - $reduction);
                            } else {
                                $montantNet = max(0, $tarifCible->montant - $bourse->valeur);
                            }
                        }
                    }

                    $fe = FraisEtudiant::updateOrCreate(
                        [
                            'etudiant_id' => $etudiant->id,
                            'annee_scolaire_id' => $anneeId,
                        ],
                        [
                            'slug' => $fraisEtudiant?->slug ?? (string) Str::uuid(),
                            'frais_scolarite_id' => $tarifCible->id,
                            'bourse_etudiant_id' => $bourseId,
                            'montant_initial' => $tarifCible->montant,
                            'montant_apres_bourse' => $montantNet,
                            'frequence_paiement' => $tarifCible->frequence ?? 'trimestriel',
                            'type_paiement' => $fraisEtudiant?->type_paiement ?? 'tranches_globales',
                            'statut' => $fraisEtudiant?->statut ?? 'en_cours',
                        ]
                    );

                    // Régénérer les échéances selon les tranches du nouveau tarif
                    $echeancier = Echeancier::updateOrCreate(
                        ['frais_etudiant_id' => $fe->id],
                        [
                            'slug' => (string) Str::uuid(),
                            'date_creation' => now(),
                            'commentaire' => 'Généré automatiquement suite à la synchronisation des tarifs.',
                        ]
                    );

                    // Supprimer les anciennes échéances non payées
                    $fe->echeances()->delete();

                    $tranches = $tarifCible->tranchepaiement;
                    $ratio = $tarifCible->montant > 0 ? ($montantNet / $tarifCible->montant) : 1;

                    if ($tranches && $tranches->count() > 0) {
                        $idx = 0;
                        foreach ($tranches as $tranche) {
                            Echeance::create([
                                'slug' => (string) Str::uuid(),
                                'echeancier_id' => $echeancier->id,
                                'frais_etudiant_id' => $fe->id,
                                'libelle' => $tranche->libelle,
                                'montant' => round($tranche->montant * $ratio),
                                'date_limite' => $tranche->date_limite,
                                'ordre' => $idx++,
                                'statut' => 'en_attente',
                                'montant_paye' => 0,
                                'reste_a_payer' => round($tranche->montant * $ratio),
                            ]);
                        }
                    } else {
                        // Tranche unique annuelle
                        Echeance::create([
                            'slug' => (string) Str::uuid(),
                            'echeancier_id' => $echeancier->id,
                            'frais_etudiant_id' => $fe->id,
                            'libelle' => 'Paiement Unique',
                            'montant' => $montantNet,
                            'date_limite' => now()->addMonths(1),
                            'ordre' => 0,
                            'statut' => 'en_attente',
                            'montant_paye' => 0,
                            'reste_a_payer' => $montantNet,
                        ]);
                    }
                });
            }

            $nbMisAJour++;
        }

        $this->newLine();
        $this->info("Synchronisation terminée avec succès :");
        $this->info("- Mis à jour : {$nbMisAJour}");
        $this->info("- Déjà conformes : {$nbInchanges}");
        if ($nbIgnoresPaiement > 0) {
            $this->warn("- Ignorés avec paiements : {$nbIgnoresPaiement}");
        }

        return 0;
    }
}
