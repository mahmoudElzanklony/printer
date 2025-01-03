<?php

namespace App\Actions;

use App\Models\User;
use App\Services\Messages;

class FailIfUserFoundAction
{
    public static function find($search)
    {
        $user = User::query()->whereRaw('email = ? or phone = ?', [$search,$search])->first();
        if($user){
            abort(Messages::error(__('errors.user_found')));
        }
    }
}
