<?php

namespace App\Console\Commands;

use App\Models\{CahierTexte, EmploiDuTemp, User, Etudiant, ClassCommitteeMember};
use App\Notifications\NotificationBase;
use Illuminate\Console\Command;

class NotifyCahierIncoherences extends Command
{
    protected $signature = 'notify:cahier-incoherences';
    protected $description = 'Notify concerned parties about incoherences in cahier de texte';

    public function handle(): int
    {
        $toNotify = CahierTexte::where('incoherent', true)->whereNull('notified_at')->get();
        foreach ($toNotify as $c) {
            $emploi = EmploiDuTemp::with('owner','group')->find($c->emploi_du_temps_id);
            $title = 'Incohérence cahier de texte';
            $content = 'Une incohérence a été signalée: ' . (string) $c->incoherence_notes;

            // notify teacher
            if ($emploi && $emploi->owner_type === User::class) {
                $prof = User::find($emploi->owner_id);
                if ($prof) { try { $prof->notify(new NotificationBase($title, $content, 2)); } catch(\Throwable $e) {} }
            }

            // notify committee members
            if ($emploi?->group_id) {
                $committee = ClassCommitteeMember::where('group_id', $emploi->group_id)->where('active', true)->get();
                foreach ($committee as $member) {
                    $memberUserId = Etudiant::where('id', $member->etudiant_id)->value('user_id');
                    if ($memberUserId) { $user = User::find($memberUserId); if ($user) { try { $user->notify(new NotificationBase($title, $content, 2)); } catch(\Throwable $e) {} } }
                }
            }

            // mark notified
            try { $c->update(['notified_at' => now()]); } catch(\Throwable $e) {}
        }

        $this->info('Notifications envoyées: ' . $toNotify->count());
        return self::SUCCESS;
    }
}
