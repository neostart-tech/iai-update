<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class NotificationBase extends Notification implements ShouldQueue
{
	use Queueable;

	static string $icon = '<svg class="pc-icon text-primary"><use xlink:href="#custom-layer"></use></svg>';

	public string $title;

	public string $content;

	public int $level;

	public function __construct(string $title = 'title', string $content = 'content', int $level = 1)
	{
		$this->title = $title;
		$this->content = $content;
		$this->level = $level;
	}

	public function via(object $notifiable): array
	{
		return ['mail', 'database'];
	}

	public function toMail(object $notifiable): Mailable
	{
		return (new Mailable());
	}

	public function toArray(object $notifiable): array
	{
		return [
			//
		];
	}

	public function toDatabase($notifiable): array
	{
		return [
			'icon' => static::$icon,
			'title' => $this->title,
			'content' => $this->content,
			'level' => $this->getLevelColor()
		];
	}

	protected final function getLevelColor(): string
	{
		return match ($this->level) {
			2 => 'warning',
			3 => 'danger',
			default => 'info'
		};
	}
}
