<?php

namespace Infoamin\Installer\Http\Middleware;

use Closure;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (env('INSTALL_APP_SECRET') && env('APP_INSTALL')) {
            return redirect(url('/'));
        } elseif (!env('INSTALL_APP_SECRET') && env('APP_INSTALL')) {
            if(auth('admin')->check()) {
                return redirect('install/verify-envato-purchase-code');
            }
            return redirect(url('/'));
        }

        return $next($request);
    }
}
