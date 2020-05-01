<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Resources\Api\MicroblogResource;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Microblog\LoadUserScope;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\OrderById;
use Coyote\Repositories\Criteria\Microblog\WithTag;

class HomeController extends Controller
{
    use CacheFactory;

    /**
     * @var MicroblogRepository
     */
    private $microblog;

    /**
     * @param MicroblogRepository $microblog
     */
    public function __construct(MicroblogRepository $microblog)
    {
        parent::__construct();

        $this->microblog = $microblog;
        $this->breadcrumb->push('Mikroblog', route('microblog.home'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->microblog->pushCriteria(new LoadUserScope($this->userId));
        $this->microblog->pushCriteria(new OrderById());

        $paginator = $this->microblog->paginate(10);
        $this->microblog->resetCriteria();

        $this->microblog->pushCriteria(new LoadUserScope($this->userId));

        /** @var \Illuminate\Database\Eloquent\Collection $microblogs */
        $microblogs =  $paginator->keyBy('id');
        $comments = $this->microblog->getTopComments($microblogs->keys());

        $this->microblog->resetCriteria();

        foreach ($comments->groupBy('parent_id') as $relations) {
            /** @var \Coyote\Microblog $microblog  */
            $microblog = &$microblogs[$relations[0]->parent_id];
            $microblog->setRelation('comments', $relations);
        }

        $paginator->setCollection($microblogs);

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

        $this->microblog->pushCriteria(new WithTag($tag));
        return $this->index();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function mine()
    {
        $this->breadcrumb->push('Moje wpisy', route('microblog.mine'));

        $this->microblog->pushCriteria(new OnlyMine($this->userId));
        return $this->index();
    }

    /**
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $this->microblog->pushCriteria(new LoadUserScope($this->userId));
        $this->microblog->pushCriteria(new EagerLoading(['comments.user']));
        $this->microblog->pushCriteria(new EagerLoadingWithCount(['comments']));

        /** @var \Coyote\Microblog $microblog */
        $microblog = $this->microblog->findOrFail($id);
        abort_if(!is_null($microblog->parent_id), 404);

        $excerpt = excerpt($microblog->html);

        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        MicroblogResource::withoutWrapping();

        return $this->view('microblog.view')->with(['microblog' => new MicroblogResource($microblog), 'excerpt' => $excerpt]);
    }
}
