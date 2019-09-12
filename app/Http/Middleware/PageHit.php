<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

class PageHit
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $result = $next($request);

        // on production environment: store hit in redis
        app('redis')->sadd('hits', $this->getPayload($request));

        // only for developing purposes. it increases counter every time user hits the page
        if (app()->environment('local', 'dev')) {
            $this->kernel->call('coyote:counter');
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getPayload(Request $request): string
    {
        return serialize([
            'path'          => trim($request->path(), '/'),
            'timestamp'     => (int) (round(time() / 60) * 60),
            'user_id'       => empty($request->user()) ? null : $request->user()->id,
            'guest_id'      => $request->session()->get('guest_id')
        ]);
    }
}
