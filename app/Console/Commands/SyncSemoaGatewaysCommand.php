<?php

namespace App\Console\Commands;

use App\Services\SemoaService;
use Illuminate\Console\Command;

class SyncSemoaGatewaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'semoa:sync-gateways';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les passerelles de paiement (gateways) depuis l\'API Semoa vers la base de données locale.';

    /**
     * Execute the console command.
     */
    public function handle(SemoaService $semoaService): int
    {
        $this->info('Début de la synchronisation des passerelles Semoa...');

        try {
            $count = $semoaService->syncGateways();
            $this->info("Succès : {$count} passerelle(s) Semoa enregistrée(s)/mise(s) à jour.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erreur lors de la synchronisation : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
