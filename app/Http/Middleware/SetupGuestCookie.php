<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class SetupGuestCookie
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
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if (!$request->cookie('guest_id')) {
            $response->cookie('guest_id', (string) Uuid::uuid4(), 525948); // 1 year
        }

        return $response;
    }
}
