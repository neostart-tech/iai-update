<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use App\Services\FraisEtudiantService;

class SyncFraisScholarships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'frais:sync-scholarships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les frais de scolarité en tenant compte des bourses assignées aux étudiants.';

    /**
     * @var FraisEtudiantService
     */
    protected $fraisService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FraisEtudiantService $fraisService)
    {
        parent::__construct();
        $this->fraisService = $fraisService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $anneeActive = AnneeScolaire::where('active', true)->first();
        
        if (!$anneeActive) {
            $this->error('Aucune année scolaire active trouvée.');
            return 1;
        }

        $this->info("Début de la synchronisation pour l'année : " . $anneeActive->libelle);

        // On ne traite que les étudiants qui ont un groupe pour l'année active
        $etudiants = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeActive) {
            $q->where('annee_scolaire_id', $anneeActive->id);
        })->get();

        $bar = $this->output->createProgressBar(count($etudiants));
        $bar->start();

        foreach ($etudiants as $etudiant) {
            $this->fraisService->assignDefaultFrais($etudiant, $anneeActive->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Synchronisation terminée avec succès.');

        return 0;
    }
}
