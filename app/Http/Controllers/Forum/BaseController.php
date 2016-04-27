<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;

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
            $parent = $this->forum->find($forum->parent_id, ['path', 'name']);
            $this->breadcrumb->push($parent->name, route('forum.category', [$parent->path]));
        }

        $this->breadcrumb->push($forum->name, route('forum.category', [$forum->path]));
    }

    /**
     * @param string|null $view
     * @param array $data
     * @return $this
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
        $groupsId = [];

        if (auth()->check()) {
            $groupsId = auth()->user()->groups()->lists('id')->toArray();
        }

        $this->forum->pushCriteria(new OnlyThoseWithAccess($groupsId));
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
        return app('cache')->remember('forum:tags', 60 * 24, function () {
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
}
