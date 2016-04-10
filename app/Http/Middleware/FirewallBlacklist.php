<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface as Firewall;

class FirewallBlacklist
{
    /**
     * @var Firewall
     */
    private $firewall;

    /**
     * FirewallBlacklist constructor.
     * @param Firewall $firewall
     */
    public function __construct(Firewall $firewall)
    {
        $this->firewall = $firewall;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $this->firewall->filter(auth()->check() ? auth()->user()->id : null, $request->ip());

        if ($response) {
            // show ban message and exit the program. I don't know how to stop application. return false didn't work :(
            echo view('errors.forbidden', $response);
            exit;
        }

        return $next($request);
    }
}
