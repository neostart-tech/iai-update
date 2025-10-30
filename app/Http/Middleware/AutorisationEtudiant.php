<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutorisationEtudiant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
      $etudiant=Auth::guard('etudiants')->user();
      if(!$etudiant || !$etudiant->estAjour()){
        return abort(403,'Vous n\'etes pas autorisé a accéder a cette page! Vous devez etre a jour dans vos paiements.');
      }

        return $next($request);
    }
}
