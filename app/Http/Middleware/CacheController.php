<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class CacheController
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param Cache $cache
     * @param Guard $auth
     */
    public function __construct(Cache $cache, Guard $auth)
    {
        $this->cache = $cache;
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  int      $ttl
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $ttl = null)
    {
        if ($this->auth->check()
            || $request->getQueryString()
            || $request->getMethod() !== 'GET'
            || $this->hasSettings($request->session()->getId())
            || $this->hasSessionData($request->session()->all())
        ) {
            return $next($request);
        }

        $key = 'url:' . $request->path();
        if ($this->cache->has($key)) {
            return (new Response())->setContent($this->cache->get($key));
        }

        /** @var Response $response */
        $response = $next($request);
        $this->cache->put($key, $response->getContent(), $ttl);

        return $response;
    }

    /**
     * @param string $sessionId
     * @return bool
     */
    private function hasSettings($sessionId)
    {
        return (bool) count(app('setting')->getAll(null, $sessionId));
    }

    /**
     * @param array $session
     * @return bool
     */
    private function hasSessionData($session)
    {
        unset($session['_token'], $session['_previous'], $session['PHPDEBUGBAR_STACK_DATA']);
        $emptyFlash = true;

        if (isset($session['flash'])) {
            if (is_array($session['flash']['old']) && (count($session['flash']['old']) > 0)) {
                $emptyFlash = false;
            }

            if (is_array($session['flash']['new']) && (count($session['flash']['new']) > 0)) {
                $emptyFlash = false;
            }
        }

        if ($emptyFlash) {
            unset($session['flash']);
        }

        return (bool) count($session) >= 1;
    }
}
