<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Http\Forms\Forum\PostForm;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    /**
     * @var Forum
     */
    protected $forum;

    /**
     * @var Topic
     */
    protected $topic;

    /**
     * @var Post
     */
    protected $post;

    /**
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     */
    public function __construct(Forum $forum, Topic $topic, Post $post)
    {
        parent::__construct();

        $this->public['uploadUrl'] = route('forum.upload');

        $this->forum = $forum;
        $this->topic = $topic;
        $this->post = $post;

        $this->breadcrumb->push('Forum', route('forum.home'));
    }

    /**
     * Builds breadcrumb for this category
     *
     * @param \Coyote\Forum $forum
     */
    public function breadcrumb($forum)
    {
        if ($forum->parent_id) {
            $parent = $this->forum->find($forum->parent_id, ['slug', 'name']);
            $this->breadcrumb->push($parent->name, route('forum.category', [$parent->slug]));
        }

        $this->breadcrumb->push($forum->name, route('forum.category', [$forum->slug]));
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
            'viewers' => $this->getViewers()
        ]);
    }

    /**
     * Applies repository criteria
     */
    protected function pushForumCriteria()
    {
        $this->forum->pushCriteria(new OnlyThoseWithAccess(auth()->user()));
        $this->forum->pushCriteria(new AccordingToUserOrder($this->userId));
    }

    /**
     * @return mixed
     */
    protected function getViewers()
    {
        // create view with online users
        return app('session.viewers')->render(request()->getRequestUri());
    }

    /**
     * @return mixed
     */
    protected function getTagClouds()
    {
        // let's cache tags. we don't need to run this query every time
        return $this->getCacheFactory()->remember('forum:tags', 60 * 24, function () {
            return $this->forum->getTagClouds();
        });
    }

    /**
     * @return mixed|null
     */
    protected function getUserTags()
    {
        $tags = $this->getSetting('forum.tags');

        if ($tags) {
            $tags = json_decode($tags);

            $weight = $this->forum->getTagsWeight($tags);
            $diff = array_diff($tags, array_keys($weight));

            $tags = array_merge($weight, array_combine($diff, array_fill(0, count($diff), 0)));
        }

        return $tags;
    }

    /**
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @param \Coyote\Post $post
     * @return \Coyote\Http\Forms\Forum\PostForm
     */
    protected function getForm($forum, $topic = null, $post = null)
    {
        return $this->createForm(PostForm::class, [
            'forum' => $forum,
            'topic' => $topic,
            'post' => $post
        ], [
            'url' => route('forum.post.submit', [$forum->slug, $topic ? $topic->id : null, $post ? $post->id : null]),
            'attr' => [
                'id' => 'submit-form',
                'method' => PostForm::POST
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param string $setting
     * @param int $default
     * @return int
     */
    protected function perPage(Request $request, $setting, $default)
    {
        if ($request->has('perPage')) {
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
