<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Criteria\Topic\BelongsToForum;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use Base;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @param Forum $forum
     */
    public function __construct(Forum $forum, Topic $topic)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->topic = $topic;
    }

    /**
     * @param \Coyote\Forum $forum
     * @param Request $request
     * @return $this
     */
    public function index($forum, Request $request)
    {
        // builds breadcrumb for this category
        $this->breadcrumb($forum);
        // create view with online users
        $viewers = app()->make('Session\Viewers')->render($request->getRequestUri());

        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $this->topic->pushCriteria(new BelongsToForum($forum->id));
        $topics = $this->topic->paginate(auth()->id(), $request->getSession()->getId());

        return parent::view('forum.category')->with(compact('viewers', 'forumList', 'forum', 'topics'));
    }
}
