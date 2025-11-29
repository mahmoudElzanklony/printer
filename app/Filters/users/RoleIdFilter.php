<?php

namespace App\Filters\users;

use Closure;

class RoleIdFilter
{
    public function handle($request, Closure $next)
    {
        if (request()->has('role_id')) {
            return $next($request)
                ->whereHas('roles', function ($query) {
                    $query->where('roles.id', '=', request('role_id'));
                });
        }
        return $next($request);
    }
}

