<?php

namespace App\Http\Controllers\Auth;

use App\Actions\DefaultInfoWithUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\categories;
use App\Models\User;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $check = auth('web')->attempt($data);
            if($check){
                $user = auth()->user();
            }else{
                $user = null;
            }

        }
        if($user){
            $user['token'] = $user->createToken($data['email'] ?? $data['phone'])->plainTextToken;

            array_merge($user->toArray(),DefaultInfoWithUser::execute($user)->toArray());
            return Messages::success(__('messages.login_successfully'),UserResource::make($user));
        }else{
            return Messages::error(__('errors.email_or_password_is_not_correct'));
        }
    }

    public function logout()
    {
        auth('web')->logout();
        return Messages::success(__('messages.logout_successfully'));
    }

    public function get_user_by_token(){
        if(request()->hasHeader('Authorization')) {
            $token = request()->header('Authorization');
            if ($token) {
                try{
                    [$id, $user_token] = explode('|', $token, 2);
                    $token_data = DB::table('personal_access_tokens')->where('token', hash('sha256', $user_token))->first();
                    if($token_data) {
                        $user_id = $token_data->tokenable_id; // !!!THIS ID WE CAN USE TO GET DATA OF YOUR USER!!!
                        $user = User::query()->find($user_id);
                        $user['token'] = request()->header('Authorization');
                        $user['token'] =  str_replace('Bearer ', '', $user['token']);
                        array_merge($user->toArray(),DefaultInfoWithUser::execute($user)->toArray());
                        return Messages::success('', UserResource::make($user));
                    }
                }catch (\Exception $e){
                    return Messages::error('not valid token',401);
                }
            }
            return Messages::error('not valid token',401);


        }
    }

}
