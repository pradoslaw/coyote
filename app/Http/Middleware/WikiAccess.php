<?php

namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Illuminate\Contracts\Auth\Access\Gate;

class WikiAccess extends AbstractMiddleware
{
    /**
     * @var WikiRepository
     */
    protected $wiki;

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @param WikiRepository $wiki
     * @param Gate $gate
     */
    public function __construct(WikiRepository $wiki, Gate $gate)
    {
        $this->wiki = $wiki;
        $this->gate = $gate;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param   string  $ability
     * @return mixed
     */
    public function handle($request, Closure $next, $ability = '')
    {
        $result = $this->wiki->findByPath(trim($request->route('path'), '/'));

        if (empty($result) || (!is_null($result->deleted_at) && $this->gate->denies($ability))) {
            abort(404);
        }

        $request->wiki = $result;

        return $next($request);
    }
}
