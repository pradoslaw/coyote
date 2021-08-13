<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\TagResource;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    /**
     * @var ForumRepository
     */
    protected $forum;

    /**
     * @var TopicRepository
     */
    protected $topic;

    /**
     * @var PostRepository
     */
    protected $post;

    /**
     * @var TagRepository
     */
    protected $tag;

    /**
     * @param ForumRepository $forum
     * @param TopicRepository $topic
     * @param PostRepository $post
     * @param TagRepository $tag
     */
    public function __construct(ForumRepository $forum, TopicRepository $topic, PostRepository $post, TagRepository $tag)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->topic = $topic;
        $this->post = $post;
        $this->tag = $tag;

        $this->middleware(function (Request $request, $next) {
            $this->breadcrumb->push('Forum', route('forum.home'));
            $forum = $request->route('forum');

            if ($forum instanceof Forum) {
                $this->breadcrumb($forum);
            }

            return $next($request);
        });

        TagResource::urlResolver(fn ($name) => route('forum.tag', [urlencode($name)]));
    }

    /**
     * Builds breadcrumb for this category
     *
     * @param \Coyote\Forum $forum
     */
    public function breadcrumb($forum)
    {
        if ($forum->parent_id) {
            $this->breadcrumb->push($forum->parent->name, UrlBuilder::forum($forum->parent));
        }

        $this->breadcrumb->push($forum->name, UrlBuilder::forum($forum));
    }

    /**
     * Renders view with breadcrumb
     *
     * @param string|null $view
     * @param array $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function view($view = null, $data = [])
    {
        return parent::view($view, $data)->with([
            'tags' => [
                'popular'   => $this->getTagClouds(),
                'user'      => $this->getUserTags()
            ],
            'viewers' => $this->getViewers(),
            'sidebar' => $this->getSetting('forum.sidebar', true)
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

    /**
     * @return mixed
     */
    protected function getViewers()
    {
        // create view with online users
        return app('session.viewers')->render($this->request->getRequestUri());
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

    /**
     * @param Request $request
     * @param string $setting
     * @param int $default
     * @return int
     */
    protected function perPage(Request $request, $setting, $default)
    {
        if ($request->filled('perPage')) {
            $perPage = max(10, min($request->get('perPage'), 50));

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
        return $this->perPage($request, 'forum.posts_per_page', 10);
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
