<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Session;

class Guest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($guard == 'users')
        {
            if (!Auth::check())
            {
                return \Redirect::guest('/login');
            }
        }
        elseif ($guard == 'admin')
        {
            if (!auth('admin')->check())
            {
                return redirect()->route('admin');
            }
        }
        return $next($request);
    }
}
