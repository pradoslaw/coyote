<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Illuminate\Http\Request;

class HomeController extends Controller
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
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));

        $this->pushForumCriteria();
        // execute query: get all categories that user can has access
        $sections = $this->forum->groupBySections(auth()->id(), $request->session()->getId());

        // create view with online users
        $viewers = app()->make('Session\Viewers')->render($request->getRequestUri());

        return parent::view('forum.home')->with(compact('sections', 'viewers'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app()->make('Parser\Forum');
        return response($parser->parse($request->get('text')));
    }
}
