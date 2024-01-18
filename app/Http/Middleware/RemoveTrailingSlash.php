<?php
namespace Coyote\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

class RemoveTrailingSlash
{
    public function handle(Request $request, callable $next): HttpFoundation\Response
    {
        $pathInfo = $request->getRequestUri();
        if ($pathInfo === '/Forum/') {
            return redirect('/Forum', status:301);
        }
        return $next($request);
    }
}
