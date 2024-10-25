<?php

namespace App\Http\Middleware;

use Closure;

class SessionCheckForAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //need to logout when session has expired
        // used 'default_currency' because both admin and user has this session
        if (auth()->check())
        {
            if (!session()->has('default_currency'))
            {
                auth()->logout();
            }
        }
        return $next($request);
    }
}
