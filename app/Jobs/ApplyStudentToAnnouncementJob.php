<?php

namespace App\Jobs;

use App\Models\{Announcement, AnnouncementEtudiant, Etudiant};
use App\Notifications\Etudiants\DepotCandidatureDoneNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyStudentToAnnouncementJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(private readonly Etudiant $etudiant, private readonly Announcement $announcement, private readonly AnnouncementEtudiant $announcementEtudiant)
	{
	}

	public function handle(): void
	{
		$this->etudiant->notify(new DepotCandidatureDoneNotification($this->announcement, $this->etudiant->album->getAttribute('cv')));
		$this->announcementEtudiant->update(['applied' => true]);
	}
}
