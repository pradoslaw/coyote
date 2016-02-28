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
        $user = $request->user();

        if ($response = $this->firewall->filter($user ? $user->id() : null, $request->ip())) {
            echo view('errors.403', $response);
            exit;
        }

        return $next($request);
    }
}
