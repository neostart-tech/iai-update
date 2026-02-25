<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Echeance;
use App\Models\Etudiant;
use App\Notifications\RappelEcheanceNotification;
use Carbon\Carbon;

class NotifierEcheances extends Command
{
    protected $signature = 'echeances:notifier';
    protected $description = 'Notifier les étudiants des échéances à venir et en retard';

    public function handle()
    {
        $this->info('Début de la notification des échéances...');

       
        $dateCible = Carbon::now()->addDays(3)->toDateString();
        $echeancesAVenir = Echeance::with(['fraisEtudiant.etudiant'])
            ->whereDate('date_limite', $dateCible)
            ->where('statut', '!=', 'paye')
            ->get();

        $this->info("Trouvé " . count($echeancesAVenir) . " échéances à J-3");

        foreach ($echeancesAVenir as $echeance) {
            $this->notifierEcheance($echeance, 'rappel_3_jours');
        }

        // ===========================================
        // 2. Notifications 3 jours après échéance (retard)
        // ===========================================
        $dateRetard = Carbon::now()->subDays(3)->toDateString();
        $echeancesEnRetard = Echeance::with(['fraisEtudiant.etudiant'])
            ->whereDate('date_limite', $dateRetard)
            ->where('statut', '!=', 'paye')
            ->get();

        $this->info("Trouvé " . count($echeancesEnRetard) . " échéances en retard");

        foreach ($echeancesEnRetard as $echeance) {
            $this->notifierEcheance($echeance, 'retard_3_jours');
        }

        $this->info('Terminé!');

        return Command::SUCCESS;
    }

    private function notifierEcheance($echeance, $type)
    {
        $etudiant = $echeance->fraisEtudiant->etudiant;
        
        if (!$etudiant) return;

        // Vérifier si l'étudiant a un user associé pour les notifications
        $user = $etudiant->user; // Supposons que Etudiant a une relation user
        
        if ($user) {
            try {
                $user->notify(new RappelEcheanceNotification($echeance, $type));
                $this->info("Notification envoyée à {$etudiant->nom} {$etudiant->prenom}");
            } catch (\Exception $e) {
                $this->error("Erreur pour {$etudiant->nom}: " . $e->getMessage());
            }
        } else {
            $this->warn("Pas d'utilisateur pour {$etudiant->nom} {$etudiant->prenom}");
        }
    }
}