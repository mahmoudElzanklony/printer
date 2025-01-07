<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Messages;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //
    public function login()
    {
        if(request()->filled('email')){
            $data = ['email'=>request('email')];
            $user = User::query()->where('email',$data['email'])->first();
        }else if(request()->filled('phone') && request()->filled('password')){
            $data = ['phone'=>request('phone') , 'password'=>request('password')];
            $check = auth()->attempt($data);
            if($check){
                $user = auth()->user();
            }else{
                $user = null;
            }
        }
        if($user){
            $user['token'] = $user->createToken($data['email'] ?? $data['phone'])->plainTextToken;
            return Messages::success(__('messages.login_successfully'),UserResource::make($user));
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
