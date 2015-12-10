<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Illuminate\Http\Request;

class HomeController extends Controller
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
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));

        if (auth()->check()) {
            $groupsId = auth()->user()->groups()->lists('id');

            if ($groupsId) {
                $this->forum->pushCriteria(new OnlyThoseWithAccess($groupsId->toArray()));
            }
        }

        // execute query: get all categories that user can has access
        $sections = $this->forum->groupBySections(auth()->id(), $request->session()->getId());

        // create view with online users
        $viewers = app()->make('Session\Viewers')->render($request->getRequestUri());

        return parent::view('forum.home')->with(compact('sections', 'viewers'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function submit($forum)
    {
        $this->breadcrumb->push([
            'Forum'      => route('forum.home'),
            $forum       => route('forum.home') . "/$forum",
            'Nowy wątek' => route('forum.submit', ['forum' => $forum])
        ]);

        return parent::view('forum.submit');
    }

    public function save($forum)
    {
    }
}
