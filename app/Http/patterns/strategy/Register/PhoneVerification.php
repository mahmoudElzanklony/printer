<?php

namespace App\Http\patterns\strategy\Register;

use App\Actions\FailIfUserFoundAction;
use App\Actions\VerificationCodeAction;
use App\Http\patterns\strategy\Messages\SMSMessages;
use App\Models\User;
use App\Services\SendEmail;
use App\Http\patterns\strategy\Register\RegisterUserOrUpdateOTPTrait;

class PhoneVerification implements VerificationInterface
{

    use RegisterUserOrUpdateOTPTrait;
    public function verify(string $verifiable , $auto_register = true)
    {
        $data = VerificationCodeAction::generateCode();
        $data['to'] = $verifiable;
        $message = new SMSMessages();
        $this->handle($auto_register,$verifiable,$data['otp_secret']);
        $message->send($data);
        return $data['otp_secret'];
    }
}
