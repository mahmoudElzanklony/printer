<?php


namespace App\Filters;
use Closure;

class SerialFilter
{
    public function handle($request, Closure $next){
        if(request()->has('serial')){
            return $next($request)
                ->where('serial','LIKE','%'.request('serial').'%');
        }
        return $next($request);
    }
}
