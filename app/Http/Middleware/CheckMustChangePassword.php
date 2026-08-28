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
        $user = $request->user();

        // Fallback for sanctum guard if not resolved automatically
        if (!$user) {
            $user = auth('sanctum')->user();
        }

        \Illuminate\Support\Facades\Log::info('CheckMustChangePassword', [
            'user' => $user ? get_class($user) : 'null',
            'must_change_password' => $user ? $user->must_change_password : null,
            'path' => $request->path()
        ]);

        // Si l'utilisateur est connecté et doit changer son mot de passe
        if ($user && $user->must_change_password) {
            // Permettre l'accès uniquement aux routes de changement de mot de passe et de déconnexion
            // Assurez-vous d'adapter ces chemins s'ils sont différents
            $allowedPatterns = [
                'api/change-password',
                'api/logout',
                'api/login',
                'api/espace-etudiant/me-connecter',
                'api/espace-etudiant/logout',
                'api/parametre/configuration',
                'api/user'
            ];

            $isAllowed = false;
            foreach ($allowedPatterns as $pattern) {
                if ($request->is($pattern)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return response()->json([
                    'message' => 'Veuillez changer votre mot de passe pour continuer.',
                    'must_change_password' => true
                ], 403);
            }
        }

        return $next($request);
    }
}
