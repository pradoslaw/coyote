<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Http\Factories\GateFactory;

class AdmAccess
{
    use GateFactory;
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param   bool    $isLogged
     * @return mixed
     */
    public function handle($request, Closure $next, $isLogged)
    {
        if ($this->getGateFactory()->denies('adm-access')) {
            abort(401);
        }

        // admin panel sometimes requires to re-enter the password
        // if session has the key "admin", that means password has been re-entered
        // and we can show the page. otherwise we have to redirect to form where user
        // can re-enter his password
        if ($isLogged && !$request->session()->has('admin')) {
            return redirect()->route('adm.home');
        }

        return $next($request);
    }
}
