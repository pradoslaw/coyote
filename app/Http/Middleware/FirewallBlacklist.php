<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Exceptions\ForbiddenException;
use Coyote\Services\Firewall\Rules;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class FirewallBlacklist
{
    /**
     * @var Rules
     */
    private $rules;

    /**
     * @var Guard
     */
    private $auth;

    /**
     * @param Rules $rules
     * @param Guard $auth
     */
    public function __construct(Rules $rules, Guard $auth)
    {
        $this->rules = $rules;
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
        $firewall = $this->rules->find($request);

        if ($firewall !== null) {
            throw new ForbiddenException($firewall);
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
