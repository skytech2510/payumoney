<?php

namespace App\Http\Middleware;

use App\Http\Helpers\Common;
use App\Traits\ApiResponse;
use Closure;


class CheckUserSuspended
{
    use ApiResponse;
    protected $helper;
    public function __construct()
    {
        $this->helper = new Common();
    }

    public function handle($request, Closure $next)
    {
        // if user suspended can't do any transactions
        $user = $this->helper->getUserStatus(auth()->user()->status);

        if ($user == 'Suspended') {
            $prefix = str_replace('/', '', request()->route()->getPrefix());
            if (str_contains($prefix, 'apiv2')) {
                return $this->badRequestResponse([], __("Suspended"));
            }

            return redirect('check-user-status');
        }
        return $next($request);
    }
}
