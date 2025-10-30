<?php

namespace App\Jobs;

use App\Traits\Notifications\Message\SendSmsMessageTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SmsSendingProcess implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SendSmsMessageTrait;

	/**
	 * @param string|array $recipients
	 * @param string $content
	 */
	public function __construct(private readonly string|array $recipients, private readonly string $content)
	{
	}

	public function handle(): void
	{
		$data = $this->sendMessage($this->recipients, $this->content);
		Log::info('Message sent data: ', [$data]);
	}
}
