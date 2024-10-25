<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, $role = null)
    {
        if (Auth::guard($role)->check())
        {
            if ($role == 'users')
            {
                return redirect('dashboard');
            }
            elseif ($role == 'admin')
            {
                return redirect()->route('dashboard');
            }
        }
        return $next($request);
    }
}
