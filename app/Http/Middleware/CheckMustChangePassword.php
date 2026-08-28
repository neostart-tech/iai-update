<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMustChangePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Si l'utilisateur est connecté et doit changer son mot de passe
        if ($user && $user->must_change_password) {
            // Permettre l'accès uniquement aux routes de changement de mot de passe et de déconnexion
            // Assurez-vous d'adapter ces chemins s'ils sont différents
            if (!$request->is('api/change-password') && 
                !$request->is('api/logout') && 
                !$request->is('api/login')) {
                
                return response()->json([
                    'message' => 'Veuillez changer votre mot de passe pour continuer.',
                    'must_change_password' => true
                ], 403);
            }
        }

        return $next($request);
    }
}
