<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		Schema::defaultStringLength(191);

		// Personnaliser l'URL de réinitialisation du mot de passe pour les candidats
		// Elle pointe vers la page Nuxt frontend et non le backend Laravel
		ResetPassword::createUrlUsing(function ($notifiable, string $token) {
			$frontendUrl = rtrim(env('FRONTEND_CANDIDAT_RESET_URL', 'http://localhost:3000/candidat/reinitialiser-mot-de-passe'), '/');
			return $frontendUrl . '?token=' . $token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
		});
	}
}
