<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Services\Geocoder\Location;
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
        if ($request->user() && $request->user()->location) {
            $request->attributes->add([
                'geocode' => new Location([
                    'city'          => $request->user()->location,
                    'latitude'      => $request->user()->latitude,
                    'longitude'     => $request->user()->longitude
                ])
            ]);
        } else {
            $geoIp = app('geo-ip');
            $result = $geoIp->ip($request->ip());

            if (is_array($result)) {
                $request->attributes->add(['geocode' => new Location($result)]);
            }
        }

        return $next($request);
    }
}
