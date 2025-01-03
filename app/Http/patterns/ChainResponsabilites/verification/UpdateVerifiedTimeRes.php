<?php

namespace App\Http\patterns\ChainResponsabilites\verification;

class UpdateVerifiedTimeRes extends VerificationProcessAbstract
{
    // request refer to user info
    function handle($user)
    {

        $user->phone_verified_at = now();
        $user->save();
    }
}
