<?php

namespace App\Http\Middleware;

use App\Models\RoleUser;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsProfesseur
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
           if (Auth::check()) {
   
            if (RoleUser::where('user_id',auth()->user()->id)->getAttribute('role_id') ===2) {
                return $next($request); 
            } else {
               abort(403, 'Unauthorized action.');
            }
        }
        return redirect('/login');
    }
}
