<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use Base;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @param Forum $forum
     */
    public function __construct(Forum $forum)
    {
        parent::__construct();

        $this->forum = $forum;
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

        $this->pushCriteria();
        $forumList = $this->forum->forumList();

        return parent::view('forum.category')->with(compact('viewers', 'forumList', 'forum'));
    }
}
