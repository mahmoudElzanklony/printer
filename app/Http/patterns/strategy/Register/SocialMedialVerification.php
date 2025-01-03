<?php

namespace App\Http\patterns\strategy\Register;


use App\Actions\FailIfUserFoundAction;
use App\Actions\VerificationCodeAction;
use App\Models\User;
use App\Services\SendEmail;
use App\Http\patterns\strategy\Register\RegisterUserOrUpdateOTPTrait;
class SocialMedialVerification implements VerificationInterface
{

    use RegisterUserOrUpdateOTPTrait;
    public function verify($verifiable , $auto_register = true)
    {
        $data = VerificationCodeAction::generateCode();


        $this->handle($auto_register,$verifiable,$data['otp_secret'],'email');


        SendEmail::send(title: $data['title'],body: $data['body'],to: $verifiable);
        return $data['otp_secret'];
    }
}
