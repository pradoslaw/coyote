<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GeocodeIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->location) {
            $geoIp = app('geo-ip');
            $result = $geoIp->ip($request->ip());

            if (is_array($result)) {
                $request->attributes->add($result);
            }
        }

        return $next($request);
    }
}
