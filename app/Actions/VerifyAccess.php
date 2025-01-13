<?php

namespace App\Actions;

use App\Services\Messages;

class VerifyAccess
{
    public static function execute($name)
    {


        if(auth()->check() && auth()->user()->roleName() != 'client') {
            dd($name,auth()->user()->roleName(),auth()->user()->can($name));
            if(!(auth()->user()->can($name))){
                abort(Messages::error('You do not have permission to do this action.'));
            }
        }
    }
}
