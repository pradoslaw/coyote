<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\Topic\Unanswered;
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

    private $tabs = [];

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
        $this->tabs = [
            'forum.home'            => 'Kategorie',
            'forum.all'             => 'Wszystkie',
            'forum.unanswered'      => 'Bez odpowiedzi'
        ];

        if (auth()->check()) {
            $this->tabs['forum.subscribes'] = 'Obserwowane';
            $this->tabs['forum.mine'] = 'Moje';
        }
    }

    protected function view($view = null, $data = [])
    {
        $tags = $this->getTagClouds();

        // create view with online users
        $viewers = app()->make('Session\Viewers')->render(request()->getRequestUri());
        $routeName = request()->route()->getName();

        return parent::view($view, $data)->with(compact('tags', 'viewers', 'routeName'))->with('tabs', $this->tabs);
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

        return $this->view('forum.home.categories')->with(compact('sections'));
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
     * @param int $userId
     * @param string $sessionId
     * @return $this
     */
    private function load($userId, $sessionId)
    {
        $topics = $this->topic->paginate($userId, $sessionId);
        return $this->view('forum.home.topics')->with(compact('topics'));
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function all(Request $request)
    {
        return $this->load(auth()->id(), $request->getSession()->getId());
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function unanswered(Request $request)
    {
        $this->topic->pushCriteria(new Unanswered());
        return $this->load(auth()->id(), $request->getSession()->getId());
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function mine(Request $request)
    {
        $this->topic->pushCriteria(new OnlyMine(auth()->id()));
        return $this->load(auth()->id(), $request->getSession()->getId());
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function subscribes(Request $request)
    {
        $this->topic->pushCriteria(new Subscribes(auth()->id()));
        return $this->load(auth()->id(), $request->getSession()->getId());
    }
}
