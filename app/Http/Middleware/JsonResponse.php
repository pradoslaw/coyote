<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class JsonResponse
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
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if (!$request->wantsJson()) {
            return $response;
        }

        if ($response instanceof Response) {
            $data = $response->getOriginalContent()->getData();
            $response->setContent($data);
        }

        return $response;
    }
}
