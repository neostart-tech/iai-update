<?php

namespace App\Console\Commands;

use App\Models\Agenda;
use App\Notifications\AgendaNotification;
use Illuminate\Console\Command;

class AgendaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifier:agenda';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $evenements = Agenda::where('alerte', true)
            ->whereBetween('start_time', [now(), now()->addMinutes(10)])
            ->where('user_id',auth()->user())
            ->get();

        foreach ($evenements as $event) {
            $event->user->notify(new AgendaNotification($event));
        }
    }
}
