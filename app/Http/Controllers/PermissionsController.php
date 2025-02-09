<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionDataResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $permissions = Permission::query()->get();

        return PermissionDataResource::collection($permissions);
    }
}
