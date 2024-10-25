<?php

namespace App\Http\Middleware;

use Closure;

class IpMiddleware
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
        $adminPanelIpAccessConfigs = (object) settings('admin_security');

        if ($adminPanelIpAccessConfigs->admin_access_ip_setting == 'Enabled') {
        
            $adminAcessIPs = explode(',', $adminPanelIpAccessConfigs->admin_access_ips);

            if (! in_array($request->ip(), $adminAcessIPs)) {
                auth()->guard('admin')->logout();
                return redirect('/');
            }
        }
    
        return $next($request);
    }
}
