<?php

namespace App\Http\patterns\strategy\Register;

use App\Actions\FailIfUserFoundAction;
use App\Models\User;

trait RegisterUserOrUpdateOTPTrait
{
    public function handle($auto_register , $verifiable , $otp_secret , $type = 'phone')
    {
        if($auto_register){
            FailIfUserFoundAction::find($verifiable);

            $user = User::query()->create([
                $type => $verifiable,
                'otp_secret' => $otp_secret,
            ]);
            $user->assignRole('client');
        }else{
            $user = User::query()->where($type, $verifiable)->first();
            if($user){
                $user->otp_secret = $otp_secret;
                $user->save();
            }
        }
    }
}
