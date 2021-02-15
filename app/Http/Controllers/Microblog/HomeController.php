<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Resources\MicroblogResource;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Http\Resources\UserResource;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Services\Microblogs\Builder;
use Coyote\Tag;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    use CacheFactory;

    /**
     * @var MicroblogRepository
     */
    private MicroblogRepository $microblog;

    /**
     * @var Builder
     */
    private Builder $builder;

    /**
     * @param MicroblogRepository $microblog
     * @param Builder $builder
     */
    public function __construct(MicroblogRepository $microblog)
    {
        parent::__construct();

        $this->microblog = $microblog;
        $this->breadcrumb->push('Mikroblog', route('microblog.home'));

        $this->middleware(function (Request $request, $next) {
            $this->builder = resolve(Builder::class);

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $paginator = $this->builder->orderById()->paginate();

        // let's cache microblog tags. we don't need to run this query every time
        $tags = $this->getCacheFactory()->remember('microblog:tags', 30 * 60, function () {
            return $this->microblog->getTags();
        });

        list($tech, $others) = $tags->partition(function (Tag $tag) {
            return $tag->category_id === Tag\Category::LANGUAGE;
        });

        return $this->view('microblog.home', [
            'flags'                     => $this->flags(),
            'count'                     => $this->microblog->count(),
            'count_user'                => $this->microblog->countForUser($this->userId),
            'pagination'                => new MicroblogCollection($paginator),
            'route'                     => request()->route()->getName(),
            'popular_tags'              => $this->microblog->popularTags($this->userId),
            'recommended_users'         => UserResource::collection($this->microblog->recommendedUsers($this->userId)),
            'tags'                      => [
                'tech'                  => $tech,
                'others'                => $others->splice(0, 10)
            ]
        ]);
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

        $this->builder->onlyUsers($this->auth);

        return $this->index();
    }

    /**
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $microblog = $this->builder->one($id);

        abort_if(!is_null($microblog->parent_id), 404);

        $excerpt = excerpt($microblog->html);

        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));

        MicroblogResource::withoutWrapping();

        $resource = new MicroblogResource($microblog);
        $resource->preserverKeys();

        return $this->view('microblog.view')->with([
            'flags' => $this->flags(),
            'microblog' => $resource,
            'excerpt' => $excerpt
        ]);
    }
}
