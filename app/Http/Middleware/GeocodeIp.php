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
            $result = null;

//            try {
//                $geoIp = app('geo-ip');
//                $result = $geoIp->ip($request->ip());
//            } catch (\Exception $exception) {
//                logger()->error($exception);
//            }

            $request->attributes->add(['geocode' => is_array($result) ? new Location($result) : new Location()]);
        }

        return $next($request);
    }
}
