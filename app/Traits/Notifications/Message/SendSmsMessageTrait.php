<?php

namespace App\Traits\Notifications\Message;

use Illuminate\Support\Str;

trait SendSmsMessageTrait
{
	public function sendMessage(array|string $recipients, string $message): array
	{
		return sendSmsMessage($this->formatNumbers($recipients), $message);
	}

	final public function formatNumbers(array|string $recipients): array
	{
		$numbers = [];
		if (!is_array($recipients)) {
			$numbers[] = Str::remove(' ', $recipients);
		} else {
			$numbers = collect($recipients)->map(fn(string $number) => Str::remove(' ', $number))->toArray();
		}
		return $numbers;
	}
}