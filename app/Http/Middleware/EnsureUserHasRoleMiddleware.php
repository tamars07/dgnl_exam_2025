<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::guard('web')->user();
        // dd(strtoupper($role));
        foreach($user->roles as $_role){
            // dd($role->name);
            if($_role->name == 'ADMIN' || $_role->name == strtoupper($role)){
                return $next($request);
            }
        }
        return redirect('/login');
    }
}
