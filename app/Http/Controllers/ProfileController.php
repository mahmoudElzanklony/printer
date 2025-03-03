<?php

namespace App\Http\Controllers;

use App\Actions\DefaultInfoWithUser;
use App\Actions\VerifyAccess;
use App\Http\Requests\userFormRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\upload_image;
use App\Models\orders;
use App\Models\orders_items;
use App\Models\roles;
use App\Services\Messages;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    //
    use upload_image;

    public function update_info(userFormRequest $request)
    {
        VerifyAccess::execute('pi pi-users|/users|update');
        $data = $request->validated();

        if (isset($data['phone'])) {
            if (auth()->user()->phone != $data['phone']) {
                $data['phone_verified_at'] = null;
            }
        }

        if (auth()->user()->getAuthPassword() != null && isset($data['old_password'])) {

            if (! (Hash::check($data['old_password'], auth()->user()->password))) {
                return Messages::error(__('errors.err_old_password'));
            }
        }

        if (request()->hasFile('image')) {
            $image = request()->file('image');
            $this->check_upload_image($image, 'users', auth()->user()->id, 'User');
        }

        auth()->user()->update($data);
        if (isset($data['role_id'])) {
            auth()->user()->syncRoles([]); // Remove all existing roles from the user
            // Assign the new role
            auth()->user()->assignRole(roles::query()->find($data['role_id'])->name);
        }
        $user = auth()->user()->load('image');

        array_merge($user->toArray(), DefaultInfoWithUser::execute($user)->toArray());

        return Messages::success(__('messages.updated_successfully'), UserResource::make($user));
    }

    public function statistics()
    {
        $orders = orders::query()->where('user_id', auth()->id())->count();
        $files = orders_items::query()->whereHas('order', function ($query) {
            $query->where('user_id', auth()->id());
        })->count();

        return Messages::success('', [
            'orders' => $orders,
            'files' => $files,
        ]);
    }
}
