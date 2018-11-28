<?php

namespace App\Http\Middleware;

use Closure;
use Jenssegers\Date\Date;

class SetDateLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Date::setLocale("pl");
        return $next($request);
    }
}
