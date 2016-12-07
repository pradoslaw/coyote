<?php

namespace Coyote\Http\Middleware;

use Closure;

class GeocodeIp
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
