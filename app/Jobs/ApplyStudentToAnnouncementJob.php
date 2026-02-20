<?php

namespace App\Jobs;

use App\Models\{Announcement, AnnouncementEtudiant, Etudiant};
use App\Notifications\Etudiants\DepotCandidatureDoneNotification;
use App\Notifications\Annonceur\NouvelleCandidatureNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyStudentToAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Etudiant $etudiant,
        private readonly Announcement $announcement,
        private readonly AnnouncementEtudiant $announcementEtudiant,
        private readonly ?string $cvPath = null,
        private readonly ?string $lettrePath = null
    ) {}

    public function handle(): void
    {
        $this->etudiant->notify(new DepotCandidatureDoneNotification(
            $this->announcement,
            $this->cvPath
        ));
        // Marquer comme postulé
        $this->announcementEtudiant->update([
            'applied' => true,
            'applied_at' => now()
        ]);
    }
}
