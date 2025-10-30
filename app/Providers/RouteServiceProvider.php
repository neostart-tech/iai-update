<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/officiel';

    public const CANDIDAT_HOME = '/espace-candidat/mon-dossier';

    public const ETUDIANT_HOME = '/espace-etudiant/mon-dossier';
    public const ENSEIGNANT_HOME = '/espace-enseignant/mon-emploi-de-temps';


    public const ETUDIANT_GUARD = 'etudiants';
    public const ADMIN_GUARD = 'web';
    public const CANDIDATURE_GUARD = 'web_candidatures';

    public const ENSEIGNANT_GUARD = 'enseignants';
    public const COMPTABLE_GUARD = 'comptables';
    public const COMPTABLE_HOME = 'espace-comptable/compta/espace-comptable/dashboard';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')->prefix('api')->group(base_path('routes/api.php'));

            // Attribué par défaut aux membres de l'administration
            Route::middleware('web')->group(base_path('routes/web.php'));

            // Routes pour l'espace candidat
            Route::middleware('web')->prefix('espace-candidat')->name('officiel.')->group(base_path('routes/candidature.php'));

            // Routes pour l'espace étudiant
//            Route::middleware('web')->prefix('espace-etudiant')->name('etudiants.')->group(base_path('routes/etudiant.php'));

            // Routes pour l'espace enseignant
            Route::middleware('web')->prefix('espace-enseignant')->name('enseignants.')->group(base_path('routes/professeur.php'));

            // Routes pour l'espace comptable
            Route::middleware('web')->prefix('espace-comptable')->name('comptable.')->group(base_path('routes/comptable.php'));
        });
    }
}
