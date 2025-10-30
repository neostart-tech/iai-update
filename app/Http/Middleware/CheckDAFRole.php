<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDAFRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Vérifier si l'utilisateur a le rôle DAF
        if (!$user->roles->contains('nom', 'Directeur des Affaires Financières')) {
            abort(403, 'Accès réservé au Directeur des Affaires Financières');
        }

        return $next($request);
    }
}