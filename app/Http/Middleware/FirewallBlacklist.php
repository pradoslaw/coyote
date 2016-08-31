<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Exceptions\ForbiddenException;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface as FirewallRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class FirewallBlacklist
{
    /**
     * @var FirewallRepository
     */
    private $firewall;

    /**
     * @var Guard
     */
    private $auth;

    /**
     * @param FirewallRepository $firewall
     * @param Guard $auth
     */
    public function __construct(FirewallRepository $firewall, Guard $auth)
    {
        $this->firewall = $firewall;
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->handleFirewallRules($request);

        if ($this->auth->check()) {
            $this->handleDeactivatedUsers();
            $this->handleIpAccess($request);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @throws ForbiddenException
     */
    protected function handleFirewallRules(Request $request)
    {
        $firewall = $this->firewall->filter($this->auth->id(), $request->ip());

        if ($firewall !== null) {
            throw new ForbiddenException($firewall);
        }
    }

    /**
     * Logout user if account has been deactivated
     */
    protected function handleDeactivatedUsers()
    {
        if (!$this->getUser()->is_active) {
            $this->auth->logout();
        }
    }

    /**
     * @param Request $request
     */
    protected function handleIpAccess(Request $request)
    {
        if (!empty($this->getUser()->access_ip)) {
            if (!$this->getUser()->hasAccessByIp($request->ip())) {
                $this->auth->logout();
            }
        }
    }

    /**
     * @return \Coyote\User
     */
    private function getUser()
    {
        return $this->auth->user();
    }
}
