<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
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
        $this->breadcrumb->push('Forum', route('forum.home'));
        $this->breadcrumb->push($forum->name, $forum->path);

        // create view with online users
        $viewers = app()->make('Session\Viewers')->render($request->getRequestUri());

        if (auth()->check()) {
            $groupsId = auth()->user()->groups()->lists('id');

            if ($groupsId) {
                $this->forum->pushCriteria(new OnlyThoseWithAccess($groupsId->toArray()));
            }
        }

        $forumList = $this->forum->forumList();

        return parent::view('forum.category')->with(compact('viewers', 'forumList', 'forum'));
    }
}
