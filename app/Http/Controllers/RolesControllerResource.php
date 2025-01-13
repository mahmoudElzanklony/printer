<?php

namespace App\Http\Controllers;

use App\Http\Requests\roleFormRequest;
use App\Http\Resources\RoleResource;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesControllerResource extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $data = Role::query()->whereNotIn('name',['client','admin'])->get();
        return RoleResource::collection($data);
    }


    public function save($data){
        DB::beginTransaction();
        $role = Role::query()->updateOrCreate([
                'id' => $data['id'] ?? null,
            ],[
                'name' => $data['name']
            ]
        );
        $permissions = Permission::query()->whereIn('id',$data['permissions'])->get();
        if(sizeof($permissions) > 0){
           $role->syncPermissions($permissions);
        }

        DB::commit();
        return Messages::success(__('messages.saved_successfully'),RoleResource::make($role));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(roleFormRequest $request)
    {
        //
        $data = $request->validated();

        return $this->save($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(roleFormRequest $request, string $id)
    {
        //
        $data = $request->validated();
        $data['id'] = $id;
        return $this->save($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
