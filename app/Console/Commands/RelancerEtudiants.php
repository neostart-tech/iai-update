<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Mail\RelanceTrancheMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RelancerEtudiants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
       protected $signature = 'relancer:etudiants';

    /**
     * The console command description.
     *
     * @var string
     */
  

    protected $description = 'Relancer les étudiants qui n\'ont pas encore soldé leurs tranches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Début de la relance des étudiants');
        $tranches =TranchePaiement::whereNotNull('date_limite')->get();
        $etudiants = Etudiant::whereHas('candidatures')->get();
        $today = now();

        foreach ($etudiants as $etudiant) {
            Log::info("Traitement étudiant", ['id' => $etudiant->id, 'email' => $etudiant->email]);
            foreach ($tranches as $tranche) {
                $dejaPaye =Paiement::where('etudiant_id', $etudiant->id)
                    ->where('tranche_paiement_id', $tranche->id)
                    ->sum('montant');
                
                Log::info("Tranche", ['tranche' => $tranche->libelle, 'dejaPaye' => $dejaPaye, 'montant' => $tranche->montant]);
                if ($dejaPaye < $tranche->montant) {
                    $joursRestants = Carbon::parse($tranche->date_limite)->diffInDays($today, false);
                    Log::info("Tranche non soldée", ['joursRestants' => $joursRestants]);
                    if ($joursRestants === 7) {
                        $this->envoyerMail($etudiant, "Attention : La tranche '{$tranche->libelle}' arrive à échéance dans 7 jours.");
                    } elseif ($joursRestants < 0) {
                        $this->envoyerMail($etudiant, "Rappel : La tranche '{$tranche->libelle}' est en retard. Merci de la régler rapidement.");
                    }
                }
            }
        }
        $this->info('Relance terminée.');
        Log::info('Fin de la relance des étudiants');
    }
    private function envoyerMail($etudiant, $contenu)
    {
        // Log::info('Appel envoyerMail', ['email' => $etudiant->email]);
        if ($etudiant->email) {
            // $this->info("Appel envoyerMail pour {$etudiant->email}");
            Mail::to($etudiant->email)->send(new RelanceTrancheMail($etudiant, $contenu));
            // $this->info("Mail envoyé à {$etudiant->email}");
            // Log::info('Mail envoyé', ['email' => $etudiant->email]);
        } else {
            $this->warn("Aucun email pour l'étudiant ID {$etudiant->id}");
            // Log::warning('Aucun email pour étudiant', ['id' => $etudiant->id]);
        }
    }
}
