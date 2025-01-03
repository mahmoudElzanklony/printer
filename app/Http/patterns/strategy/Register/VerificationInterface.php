<?php

namespace App\Http\patterns\strategy\Register;

use Illuminate\Foundation\Http\FormRequest;

interface VerificationInterface
{
    public function verify(String $verifiable , $auto_register = true);
}
