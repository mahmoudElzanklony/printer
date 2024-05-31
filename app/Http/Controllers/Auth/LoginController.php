<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Messages;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //
    public function login()
    {
        $data = ['email'=>request('email'),'password'=>request('password')];
        if(auth()->attempt($data)){
            $user = User::query()->where('email',$data['email'])->first();
            $user['token'] = $user->createToken($data['email'])->plainTextToken;
            return Messages::success(__('messages.login_successfully'),$user);
        }else{
            return Messages::error(__('errors.email_or_password_is_not_correct'));
        }
    }

    public function logout()
    {
        auth()->logout();
        return Messages::success(__('messages.logout_successfully'));
    }
}
