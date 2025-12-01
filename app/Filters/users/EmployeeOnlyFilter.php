<?php

namespace App\Filters\users;

use Closure;

class EmployeeOnlyFilter
{
    public function handle($request, Closure $next)
    {
        if (request()->has('employee_only') && request('employee_only')) {
            return $next($request)
                ->whereHas('roles', function ($query) {
                    $query->where('name', '!=', 'client');
                });
        }
        return $next($request);
    }
}

