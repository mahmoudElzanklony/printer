<?php

namespace App\Http\Controllers;

use App\Actions\VerifyAccess;
use App\Http\Requests\userFormRequest;
use App\Http\Resources\UserResource;
use App\Models\model_has_roles;
use App\Models\roles;
use App\Models\User;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    //
    public function update_info(userFormRequest $request)
    {
        VerifyAccess::execute('pi pi-users|/users|update');
        $data = $request->validated();

        if(isset($data['phone'])){
            if(auth()->user()->phone != $data['phone']){
                $data['phone_verified_at'] = null;
            }
        }

        if(auth()->user()->getAuthPassword() != null && isset($data['old_password'])){

            if(!(Hash::check($data['old_password'],auth()->user()->password))){
                return Messages::error(__('errors.err_old_password'));
            }
        }

        auth()->user()->update($data);
        if(isset($data['role_id'])){
            auth()->user()->syncRoles([]); // Remove all existing roles from the user
            // Assign the new role
            auth()->user()->assignRole(roles::query()->find($data['role_id'])->name);
        }
        return Messages::success(__('messages.updated_successfully'),UserResource::make(auth()->user()));
    }
}
