<?php
namespace Coyote\Http\Middleware\Forum;

use Closure;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Eloquent\WikiRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

class WikiAccess extends AbstractMiddleware
{
    public function __construct(
        private WikiRepository $wiki,
        private Gate           $gate)
    {
    }

    public function handle(Request $request, Closure $next, $ability = ''): HttpFoundation\Response
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
            abort(404);
        }
        $request->attributes->set('wiki', $result);
        $this->wiki->resetCriteria();
        return $next($request);
    }
}
