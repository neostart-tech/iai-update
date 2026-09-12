<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Relance des tranches de paiement
        $schedule->command('relancer:etudiants')->dailyAt('07:00');

        // Notification des incohérences du cahier de texte
        $schedule->command('notify:cahier-incoherences')->dailyAt('17:00');

        // Notifications de l'agenda
        $schedule->command('notifier:agenda')->everyTenMinutes();

        // Rappel des échéances de paiement
        $schedule->command('echeances:notifier')->dailyAt('08:00');

        // Synchronisation des passerelles Semoa
        $schedule->command('semoa:sync-gateways')->dailyAt('02:00');

        // Synchronisation des bourses et frais
        $schedule->command('frais:sync-scholarships')->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
