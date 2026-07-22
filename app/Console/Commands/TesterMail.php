<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TesterMail extends Command
{
    protected $signature = 'mail:tester {email=ebenezerafantsao@gmail.com}';

    protected $description = 'Envoyer un email de test pour vérifier la configuration mail';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info("Envoi d'un email de test à {$email}...");

        Mail::raw("Ceci est un email de test envoyé depuis l'application IAI-Togo pour vérifier la configuration mail.", function ($message) use ($email) {
            $message->to($email)->subject('Test de configuration mail');
        });

        $this->info('Email envoyé avec succès.');
    }
}
