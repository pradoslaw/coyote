<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Resources\Api\MicroblogResource;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Microblog\LoadUserScope;
use Coyote\Repositories\Criteria\Microblog\LoadVoters;
use Coyote\Services\Microblogs\Builder;

class HomeController extends Controller
{
    use CacheFactory;

    /**
     * @var MicroblogRepository
     */
    private $microblog;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @param MicroblogRepository $microblog
     * @param Builder $builder
     */
    public function __construct(MicroblogRepository $microblog, Builder $builder)
    {
        parent::__construct();

        $this->microblog = $microblog;
        $this->breadcrumb->push('Mikroblog', route('microblog.home'));

        $this->builder = $builder;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $paginator = $this->builder->forUser($this->auth)->orderById()->paginate();

        // let's cache microblog tags. we don't need to run this query every time
        $tags = $this->getCacheFactory()->remember('microblog:tags', 30 * 60, function () {
            return $this->microblog->getTags();
        });

        // we MUST NOT cache popular entries because it may contains current user's data
        $popular = $this->microblog->takePopular(5);

        return $this->view('microblog.home', [
            'count'                     => $this->microblog->count(),
            'count_user'                => $this->microblog->countForUser($this->userId),
            'pagination'                => MicroblogResource::collection($paginator)->response()->getContent(),
            'route'                     => request()->route()->getName()
        ])->with(compact('tags', 'popular'));
    }

    /**
     * @param string $tag
     * @return \Illuminate\View\View
     */
    public function tag($tag)
    {
        $this->breadcrumb->push('Wpisy z tagiem: ' . $tag, route('microblog.tag', [$tag]));

        $this->builder->withTag($tag);

        return $this->index();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function mine()
    {
        $this->breadcrumb->push('Moje wpisy', route('microblog.mine'));

        $this->builder->forUser($this->auth)->onlyMine();

        return $this->index();
    }

    /**
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $this->microblog->pushCriteria(new LoadUserScope($this->userId));
        $this->microblog->pushCriteria(new LoadVoters());
        $this->microblog->pushCriteria(new EagerLoadingWithCount(['comments']));

        /** @var \Coyote\Microblog $microblog */
        $microblog = $this->microblog->findOrFail($id);
        abort_if(!is_null($microblog->parent_id), 404);

        $excerpt = excerpt($microblog->html);

        $microblog->load(['comments' => function ($builder) {
            return $builder->select('microblogs.*')->includeIsVoted($this->userId)->includeVoters()->with('user');
        }]);

        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        MicroblogResource::withoutWrapping();

        return $this->view('microblog.view')->with(['microblog' => new MicroblogResource($microblog), 'excerpt' => $excerpt]);
    }
}
