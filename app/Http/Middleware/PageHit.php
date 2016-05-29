<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Contracts\Console\Kernel;

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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $result = $next($request);

        // on production environment: store hit in redis
        app('redis')->sadd(
            'hit:' . $request->path(),
            (empty($request->user()) ? $request->session()->getId() : $request->user()->id) . ';' . round(time() / 300) * 300
        );

        // only for developing purposes. it increases counter every time user hits the page
        if (app()->environment('local', 'dev')) {
            $this->kernel->call('coyote:counter');
        }

        return $result;
    }
}
