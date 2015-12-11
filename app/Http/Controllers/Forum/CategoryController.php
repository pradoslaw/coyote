<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $forum;

    public function __construct(Forum $forum)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->middleware('forum.access');
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


        return parent::view('forum.category')->with('viewers', $viewers);
    }
}
