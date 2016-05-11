<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;

class ShowErrorIfNotFound extends AbstractMiddleware
{
    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @param WikiRepository $wiki
     */
    public function __construct(WikiRepository $wiki)
    {
        $this->wiki = $wiki;
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
        $result = $this->wiki->findBy('slug', trim($request->path(), '/'));
        if (empty($result)) {
            abort(404);
        }

        $request->wiki = $result;

        return $next($request);
    }
}
