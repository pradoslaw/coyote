<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Illuminate\Http\Request;

class HomeController extends Controller
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
     * @param Topic $topic
     */
    public function __construct(Forum $forum, Topic $topic)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->topic = $topic;

        $this->breadcrumb->push('Forum', route('forum.home'));
    }

    protected function view($view = null, $data = [])
    {
        $tags = $this->getTagClouds();

        // create view with online users
        $viewers = app()->make('Session\Viewers')->render(request()->getRequestUri());

        return parent::view($view, $data)->with(compact('tags', 'viewers'));
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        $this->pushForumCriteria();
        // execute query: get all categories that user can has access
        $sections = $this->forum->groupBySections(auth()->id(), $request->session()->getId());

        return $this->view('forum.home')->with(compact('sections'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app()->make('Parser\Post');
        return response($parser->parse($request->get('text')));
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function all(Request $request)
    {
        $topics = $this->topic->paginate(auth()->id(), $request->getSession()->getId());
        return $this->view('forum.all')->with(compact('topics'));
    }
}
