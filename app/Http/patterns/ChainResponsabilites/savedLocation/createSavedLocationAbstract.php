<?php

namespace App\Http\patterns\ChainResponsabilites\savedLocation;

abstract class createSavedLocationAbstract
{
    protected $next = null;
    public $lang = null;
    public $other = null;
    public function __construct()
    {
        $this->lang = request()->header('lang');
        $this->other = $this->lang == "ar" ? "en" : "ar";
    }



    public function setNext($next)
    {
        $this->next = $next;
    }
    public function handle($data)
    {
        if($this->next){
            $this->next->handle($data);
        }
    }
}
