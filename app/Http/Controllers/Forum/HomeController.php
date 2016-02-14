<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\Topic\Unanswered;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Topic\WithTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

        if ($routeName == 'forum.tag') {
            $tabs['forum.tag'] = 'Wątki z: ' . request()->route('tag');
        }
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
        // get categories collapse
        $collapse = $this->getSetting('forum.collapse');
        if ($collapse) {
            $collapse = unserialize($collapse);
        }

        return $this->view('forum.home.categories')->with(compact('sections', 'collapse'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app()->make('Parser\Post')->setEnableCache(false);
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

        // we need to get an information about flagged topics. that's how moderators can notice
        // that's something's wrong with posts.
        if (Gate::allows('forum-delete')) {
            $flags = app()->make('FlagRepository')->takeForTopics($topics->groupBy('id')->keys()->toArray());
        }

        return $this->view('forum.home.topics')->with(compact('topics', 'flags'));
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

    /**
     * @param string $name
     * @param Request $request
     * @return HomeController
     */
    public function tag($name, Request $request)
    {
        $this->topic->pushCriteria(new WithTag($name));
        return $this->load(auth()->id(), $request->getSession()->getId());
    }

    /**
     * Mark ALL categories as READ
     */
    public function mark()
    {
        $forums = $this->forum->all(['id']);
        foreach ($forums as $forum) {
            $this->forum->markAsRead($forum->id, auth()->id(), request()->session()->getId());
        }
    }
}
