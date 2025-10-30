<?php

use Intergo\SmsTo\Facades\SmsToSms;
use Intergo\SmsTo\Module\Sms\Message\{CampaignMessage, SingleMessage};

/**
 * Envoie un SMS à une ou plusieurs personnes
 *
 * @param array|string $recipients Les destinataires du SMS
 * @param string $content Le contenu du SMS à envoyer
 * @return array Le résultat de l'envoi du SMS
 * @package IAI-SITE
 * @created 2024-07-10
 * @author SOSSOU-GAH Ézéchiel
 */
if (!function_exists('sendSmsMessage')) {
	function sendSmsMessage(array|string $recipients, string $content): array
	{
		$messageConfig = (is_array($recipients) ? new CampaignMessage() : new SingleMessage())->setTo($recipients);

		$message = $messageConfig
			->setMessage($content)
			->setSenderID(env('SMSTO_SENDER_ID'));

//		if (env('APP_ENV') === 'local')
//			return SmsToSms::estimate($message);

		return SmsToSms::send($message);
	}
}