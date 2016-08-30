<?php

namespace Coyote\Http\Middleware;

use Closure;
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
     * @param  \Closure $next
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
     */
    protected function handleFirewallRules(Request $request)
    {
        $response = $this->firewall->filter($this->auth->id(), $request->ip());

        if ($response) {
            // show ban message and exit the program. I don't know how to stop application. return false didn't work :(
            // @todo maybe throw ForbiddenException ?
            echo view('errors.forbidden', $response);
            exit;
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
     * @param \Illuminate\Http\Request $request
     */
    protected function handleIpAccess($request)
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
