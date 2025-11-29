<?php

namespace App\Filters\users;

use Closure;

class RoleNameFilter
{
    public function handle($request, Closure $next)
    {
        if (request()->has('role')) {
            return $next($request)
                ->whereHas('roles', function ($query) {
                    $query->where('name', '=', request('role'));
                });
        }
        return $next($request);
    }
}

