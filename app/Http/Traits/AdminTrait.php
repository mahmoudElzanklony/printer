<?php

namespace App\Http\Traits;

use App\Models\User;

trait AdminTrait
{
    public function currentAdmin()
    {
        return User::query()->whereHas('role',function($e){
            $e->where('name','=','admin');
        })->first();
    }
}
