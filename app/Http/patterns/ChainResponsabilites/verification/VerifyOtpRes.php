<?php

namespace App\Http\patterns\ChainResponsabilites\verification;

use App\Models\User;

class VerifyOtpRes extends VerificationProcessAbstract
{

    function handle($request)
    {
        $user = User::query()
            ->whereRaw('(phone = "'.request('phone').'" OR email = "'.request('email').'")')
            ->where('otp_secret',request('otp_secret'))
                ->firstOrFailWithCustomError(__('errors.otp_not_correct'));
        if($user) {
            parent::handle($user);
        }
    }
}
