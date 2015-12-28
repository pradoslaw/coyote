<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Gate;
use Cache;

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
     * @param Forum $forum
     * @param Topic $topic
     */
    public function __construct(Forum $forum, Topic $topic)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->topic = $topic;

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
     * @param null $view
     * @param array $data
     * @return $this
     */
    protected function view($view = null, $data = [])
    {
        return parent::view($view, $data)->with([
            'tags' => $this->getTagClouds(),
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
        return app()->make('Session\Viewers')->render(request()->getRequestUri());
    }

    /**
     * @return mixed
     */
    protected function getTagClouds()
    {
        // let's cache tags. we don't need to run this query every time
        return Cache::remember('forum:tags', 60 * 24, function () {
            return $this->forum->getTagClouds();
        });
    }
}
