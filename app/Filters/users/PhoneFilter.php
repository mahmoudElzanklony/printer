<?php


namespace App\Filters\users;
use Closure;

class PhoneFilter
{
    public function handle($request, Closure $next){
        if(request()->has('phone')){
            return $next($request)
                ->where('phone','LIKE','%'.request('phone').'%');
        }
        return $next($request);
    }
}
