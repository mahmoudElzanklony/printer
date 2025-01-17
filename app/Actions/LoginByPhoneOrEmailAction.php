<?php

namespace App\Actions;

use App\Models\User;

class LoginByPhoneOrEmailAction
{
    public static function login($search)
    {

        $user = User::query()
            ->whereRaw('(phone = "'.$search.'" OR email = "'.$search.'")')
            ->firstOrFailWithCustomError(__('errors.not_found_data'));
        auth('web')->login($user);
        $user['token'] = $user->createToken($user->email != '' ? $user->email : $user->phone)->plainTextToken;
        return $user;
    }
}
