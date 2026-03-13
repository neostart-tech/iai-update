<?php

namespace App\Notifications;

use App\Models\Paiement;
use App\Notifications\NotificationBase;


class PaiementNotification extends NotificationBase
{
    public static string $icon = '<i class="fas fa-info"></i>';

	public function __construct(Paiement $paiement)
	{
		parent::__construct("Paiement des frais de scolarité", "Cher étudiant vous venez d'effectuer un paiement d'un montant de" . $paiement->montant);
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}
}
