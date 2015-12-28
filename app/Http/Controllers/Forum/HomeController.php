<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\Topic\Unanswered;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    /**
     * @param null $view
     * @param array $data
     * @return mixed
     */
    protected function view($view = null, $data = [])
    {
        $tabs = [
            'forum.home'            => 'Kategorie',
            'forum.all'             => 'Wszystkie',
            'forum.unanswered'      => 'Bez odpowiedzi'
        ];

        if (auth()->check()) {
            $tabs['forum.subscribes'] = 'Obserwowane';
            $tabs['forum.mine'] = 'Moje';
        }

        $routeName = request()->route()->getName();
        return parent::view($view, $data)->with(compact('routeName', 'tabs'));
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
        $groupsId = [];

        if (auth()->check()) {
            $groupsId = auth()->user()->groups()->lists('id')->toArray();
        }

        $this->topic->pushCriteria(new OnlyThoseWithAccess($groupsId));

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
