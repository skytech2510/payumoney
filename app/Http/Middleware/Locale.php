<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Session;
use Illuminate\Support\Facades\Config;

class Locale
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
        $prefix = str_replace('/', '', request()->route()->getPrefix());
        if ($prefix !== config('adminPrefix'))
        {
            if (env('APP_INSTALL'))
            {
                if (!session('dflt_lang'))
                {
                    $lang = getLanguageDefault();
                    if (!empty($lang))
                    {
                        session(['dflt_lang' => $lang->short_name]);
                    }
                }
            }

            if (Session::get('dflt_lang'))
            {
                App::setLocale(Session::get('dflt_lang'));
            }
            else
            {
                if (env('APP_INSTALL'))
                {
                    $lang = getLanguageDefault();
                    if (!empty($lang))
                    {
                        App::setLocale($lang->short_name);
                        session(['dflt_lang' => $lang->short_name]);
                    }
                }
            }
        }
        return $next($request);
    }
}
