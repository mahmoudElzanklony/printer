<?php

namespace App\Actions;

class VerificationCodeAction
{
    public static function generateCode(){
        $data['otp_secret'] = rand(1000, 9999);
        $data['title'] =  __('keywords.otp_secret').' '.__('keywords.inside').' '.env('APP_NAME');
        $data['body'] =  __('keywords.your_otp').' '.$data['otp_secret'];
        return $data;
    }
}
