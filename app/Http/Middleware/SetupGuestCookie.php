<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class SetupGuestCookie
{
    /**
     * @var string
     */
    private $cookieName;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $this->cookieName = config('session.guest_cookie');

        // establish quest id
        $guestId = $this->getGuestIdValue($request);
        // cookie value
        $cookie = $request->cookie($this->cookieName);

        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if (method_exists($response, 'cookie') && ($cookie === null || $cookie !== $guestId)) {
            $response->cookie($this->cookieName, $guestId, 525948); // 1 year
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getGuestIdValue(Request $request): string
    {
        // get guest_id from session. this does not require additional query to redis.
        // this value can be null if session does not exist.
        $guestId = $request->session()->get('guest_id');

        // cookie was removed or this is users' first visit
        if ($guestId === null) {
            if (!empty($request->user())) {
                $guestId = $request->user()->guest_id;
            } elseif ($this->hasValidCookie($request)) {
                $guestId = $request->cookie($this->cookieName);
            } else {
                $guestId = (string) Uuid::uuid4(); // create new uuid for this guest
            }

            $request->session()->put('guest_id', $guestId);
        }

        // validate registered user's guest_id with the one from session.
        // why? previously user could use website as anonymous user.
        if (!empty($request->user()) && $request->user()->guest_id !== $guestId) {
            $guestId = $request->user()->guest_id;
            $request->session()->put('guest_id', $guestId);
        }

        return $guestId;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function hasValidCookie(Request $request): bool
    {
        if (!$request->hasCookie($this->cookieName)) {
            return false;
        }

        return Uuid::isValid($request->cookie($this->cookieName));
    }
}
