<?php
namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\TagResource;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Eloquent\ForumRepository;
use Coyote\Repositories\Eloquent\PostRepository;
use Coyote\Repositories\Eloquent\TagRepository;
use Coyote\Repositories\Eloquent\TopicRepository;
use Coyote\Services\Session\Renderer;
use Coyote\Topic;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class BaseController extends Controller
{
    public function __construct(
        protected ForumRepository $forum,
        protected TopicRepository $topic,
        protected PostRepository  $post,
        protected TagRepository   $tag)
    {
        parent::__construct();
        $this->middleware(function (Request $request, $next) {
            $this->breadcrumb->push('Forum', route('forum.home'));
            $forum = $request->route('forum');

            if ($forum instanceof Forum) {
                $this->breadcrumb($forum);
            }

            return $next($request);
        });

        TagResource::urlResolver(fn($name) => route('forum.tag', [urlencode($name)]));
    }

    public function breadcrumb(Forum $forum): void
    {
        if ($forum->parent_id) {
            $this->pushBreadcrumb($forum->parent);
        }
        $this->pushBreadcrumb($forum);
    }

    private function pushBreadcrumb(Forum $forum): void
    {
        $this->breadcrumb->push($forum->name, route('forum.category', [$forum]));
    }

    /**
     * @inheritdoc
     */
    protected function view($view = null, $data = [])
    {
        return parent::view($view, $data)->with([
            'tags'          => [
                'popular' => $this->getTagClouds(),
                'user'    => $this->getUserTags(),
            ],
            'globalViewers' => $this->globalViewers(),
            'localViewers'  => $this->localViewers(),
            'sidebar'       => $this->getSetting('forum.sidebar', true),
        ]);
    }

    /**
     * Applies repository criteria
     *
     * @param bool $ignoreHidden
     */
    protected function pushForumCriteria(bool $ignoreHidden = false)
    {
        $this->forum->pushCriteria(new OnlyThoseWithAccess($this->auth));
        $this->forum->pushCriteria(new AccordingToUserOrder($this->userId, $ignoreHidden));
        $this->forum->pushCriteria(new EagerLoading('tags'));
    }

    private function globalViewers(): View
    {
        /** @var Renderer $renderer */
        $renderer = app(Renderer::class);
        return $renderer->render('/', local:false);
    }

    private function localViewers(): View
    {
        /** @var Renderer $renderer */
        $renderer = app(Renderer::class);
        return $renderer->render($this->request->getRequestUri(), local:true);
    }

    /**
     * @return mixed
     */
    protected function getTagClouds()
    {
        // let's cache tags. we don't need to run this query every time
        return $this->getCacheFactory()->remember('forum:tags', now()->addDay(), function () {
            return $this->tag->tagClouds(Topic::class);
        });
    }

    /**
     * @return array
     */
    protected function getUserTags()
    {
        $tags = json_decode($this->getSetting('forum.tags', '[]'));

        if (!$tags) {
            return [];
        }

        return TagResource::collection($this->tag->findWhere(['name' => $tags]))->resolve($this->request);
    }

    protected function perPage(Request $request, string $setting, int $default): int
    {
        if ($request->filled('perPage')) {
            $perPage = max(10, min((int)$request->input('perPage'), 50));

            $this->setSetting($setting, $perPage);
        } else {
            $perPage = $this->getSetting($setting, $default);
        }

        return $perPage;
    }

    /**
     * @param Request $request
     * @return int
     */
    protected function postsPerPage(Request $request)
    {
        return $this->perPage($request, 'forum.posts_per_page', 30);
    }

    /**
     * @param Request $request
     * @return int
     */
    protected function topicsPerPage(Request $request)
    {
        return $this->perPage($request, 'forum.topics_per_page', 20);
    }

    /**
     * @return array|mixed
     */
    protected function collapse()
    {
        return $this->getSetting('forum.collapse') ? unserialize($this->getSetting('forum.collapse')) : [];
    }
}
