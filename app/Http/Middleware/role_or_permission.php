<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

class role_or_permission extends RoleOrPermissionMiddleware
{

    public function handle($request, Closure $next, $roleOrPermission, $guard = null): Response
    {
        return $next($request);
    }
}
