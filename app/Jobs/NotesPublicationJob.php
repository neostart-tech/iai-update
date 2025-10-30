<?php

namespace App\Jobs;

use App\Models\{Evaluation, User};
use App\Notifications\Etudiants\NotesPublicationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class NotesPublicationJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private readonly User $user;

	public function __construct(private readonly Evaluation $evaluation)
	{
		$this->user = request()->user();
	}

	public function handle(): void
	{
		$name = $this->evaluation->getAttribute('type')->value . ' de ' . $this->evaluation->matiere->getAttribute('nom');
		Notification::send($this->evaluation->group->etudiants, $notification = new NotesPublicationNotification($name));
		$this->user->notify($notification);
	}
}
