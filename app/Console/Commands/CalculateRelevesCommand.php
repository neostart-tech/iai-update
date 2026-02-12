<?php

namespace App\Console\Commands;

use App\Models\AnneeScolaire;
use App\Models\Periode;
use App\Services\NoteCalculationService;
use Illuminate\Console\Command;

class CalculateRelevesCommand extends Command
{
    protected $signature = 'notes:calculate-releves 
                            {--annee= : ID de l\'année scolaire}
                            {--periode= : ID de la période}
                            {--etudiant= : ID de l\'étudiant (optionnel)}';
                            
    protected $description = 'Calcule les relevés de notes pour les étudiants';
    
    public function handle(NoteCalculationService $service)
    {
        $anneeId = $this->option('annee');
        $periodeId = $this->option('periode');
        $etudiantId = $this->option('etudiant');
        
        if (!$anneeId || !$periodeId) {
            $this->error('Veuillez spécifier --annee et --periode');
            return 1;
        }
        
        $annee = AnneeScolaire::find($anneeId);
        $periode = Periode::find($periodeId);
        
        if (!$annee || !$periode) {
            $this->error('Année ou période non trouvée');
            return 1;
        }
        
        if ($etudiantId) {
            $etudiant = Etudiant::find($etudiantId);
            if ($etudiant) {
                $this->info("Calcul du relevé pour l'étudiant {$etudiant->nom_complet}...");
                $service->calculateAndSaveForStudent($etudiant, $annee, $periode);
                $this->info('Terminé !');
            }
        } else {
            $this->info("Calcul de tous les relevés pour {$annee->libelle} - {$periode->nom}...");
            $service->recalculateAllForPeriode($annee, $periode);
            $this->info('Terminé !');
        }
        
        return 0;
    }
}