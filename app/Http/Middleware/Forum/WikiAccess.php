<?php

namespace Coyote\Http\Middleware\Forum;

use Closure;
use Coyote\Repositories\Contracts\WikiRepositoryInterface as WikiRepository;
use Coyote\Repositories\Criteria\WithTrashed;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;

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
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string  $ability
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $ability = '')
    {
        if ($this->gate->allows($ability)) {
            $this->wiki->pushCriteria(new WithTrashed());
        }

        $path = trim($request->route('path'), '/');
        $result = $this->wiki->findByPath($path);

        if (empty($result)) {
            $location = $this->wiki->findNewLocation($path);

            if (!empty($location)) {
                return redirect()->to($location->path);
            }

            // throw 404
            abort(404);
        }

        $request->attributes->set('wiki', $result);
        $this->wiki->resetCriteria();

        return $next($request);
    }
}
