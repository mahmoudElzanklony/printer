<?php

namespace App\Http\patterns\ChainResponsabilites\verification;

abstract class VerificationProcessAbstract
{
    public $next = null;

    public function handle($data){
        if($this->next){
            $this->next->handle($data);
        }
    }
    public function setNext($next){
        $this->next = $next;
    }
}
