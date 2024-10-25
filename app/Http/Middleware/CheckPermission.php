<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Common, Config;
use Closure;


class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    use ApiResponse;
    protected $permission;

    public function __construct(Common $permission)
    {
        $this->permission = $permission;
    }

    public function handle($request, Closure $next, $permissions)
    {
        $prefix = str_replace('/', '', request()->route()->getPrefix());
        if ($prefix == config('adminPrefix')) {
            $gaurd_type = auth('admin')->user()->id;
        } else {
            $gaurd_type = \Auth::user()->id;
        }

        if ($this->permission->has_permission($gaurd_type, $permissions)) {
            return $next($request);
        } else {
            if (str_contains($prefix, 'apiv2')) {
                return $this->forbiddenResponse([], __("Unauthorized"));
            }
            return response()->view('admin.errors.404', [], 404);
        }
    }
}
