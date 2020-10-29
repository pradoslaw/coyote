<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Resources\MicroblogResource;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Services\Microblogs\Builder;

class HomeController extends BaseController
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
            'pagination'                => new MicroblogCollection($paginator),
            'route'                     => request()->route()->getName(),
            'flags'                     => $this->flags($paginator)
        ])->with(compact('tags', 'popular'));
    }

    /**
     * @param string $tag
     * @return \Illuminate\View\View
     */
    public function tag(string $tag)
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
        $microblog = $this->builder->forUser($this->auth)->one($id);

        abort_if(!is_null($microblog->parent_id), 404);

        $excerpt = excerpt($microblog->html);

        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        MicroblogResource::withoutWrapping();

        $resource = new MicroblogResource($microblog);
        $resource->preserverKeys();

        return $this->view('microblog.view')->with([
            'microblog' => $resource,
            'excerpt' => $excerpt,
            'flags' => $this->flags($microblog)
        ]);
    }
}
