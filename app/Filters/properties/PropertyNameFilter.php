<?php


namespace App\Filters\properties;
use Closure;

class PropertyNameFilter
{
    public function handle($request, Closure $next){
        if(request()->has('name')){
            return $next($request)
                ->where('name','LIKE','%'.request('name').'%');
        }
        return $next($request);
    }
}
