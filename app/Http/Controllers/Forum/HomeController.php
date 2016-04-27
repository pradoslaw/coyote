<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\Topic\Unanswered;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Topic\WithTag;
use Illuminate\Http\Request;
use Gate;

class HomeController extends BaseController
{
    /**
     * @param string $view
     * @param array $data
     * @return mixed
     */
    protected function view($view = null, $data = [])
    {
        $route = request()->route()->getName();

        $tabs = app('menu')->make('tabs', function ($menu) {
            $tabs = [
                'forum.home'            => 'Kategorie',
                'forum.all'             => 'Wszystkie',
                'forum.unanswered'      => 'Bez odpowiedzi'
            ];

            if (auth()->check()) {
                $tabs['forum.subscribes'] = 'Obserwowane';
                $tabs['forum.mine'] = 'Moje';
            }

            foreach ($tabs as $route => $label) {
                $menu->add($label, ['route' => $route]);
            }
        });

        if ($route == 'forum.tag') {
            $tabs->add('Wątki z: ' . request()->route('tag'), [
                'route' => [
                    'forum.tag', urlencode(request()->route('tag'))
                ]
            ]);
        }

        if ($route == 'forum.user') {
            $user = app(UserRepositoryInterface::class)->find(request()->route('id'));
            $tabs->add('Posty: ' . $user->name, [
                'route' => [
                    'forum.user', request()->route('id')
                ]
            ]);
        }

        $title = '';
        foreach ($tabs->all() as $tab) {
            if ($tab->attr('class') == 'active') {
                $title = $tab->title;
            }
        }

        return parent::view($view, $data)->with(compact('route', 'tabs', 'title'));
    }

    /**
     * @return $this
     */
    public function index()
    {
        $this->pushForumCriteria();
        // execute query: get all categories that user can has access
        $sections = $this->forum->groupBySections($this->userId, $this->sessionId);
        // get categories collapse
        $collapse = $this->getSetting('forum.collapse');
        if ($collapse) {
            $collapse = unserialize($collapse);
        }

        return $this->view('forum.home')->with(compact('sections', 'collapse'));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
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
        if ($topics && Gate::allows('forum-delete')) {
            $flags = app()->make('FlagRepository')->takeForTopics($topics->groupBy('id')->keys()->toArray());
        }

        return $this->view('forum.topics')->with(compact('topics', 'flags'));
    }

    /**
     * @return $this
     */
    public function all()
    {
        return $this->load($this->userId, $this->sessionId);
    }

    /**
     * @return $this
     */
    public function unanswered()
    {
        $this->topic->pushCriteria(new Unanswered());
        return $this->load($this->userId, $this->sessionId);
    }

    /**
     * @return $this
     */
    public function mine()
    {
        $this->topic->pushCriteria(new OnlyMine($this->userId));
        return $this->load($this->userId, $this->sessionId);
    }

    /**
     * @param int $userId
     * @return HomeController
     */
    public function user($userId)
    {
        $this->topic->pushCriteria(new OnlyMine($userId));
        return $this->load($this->userId, $this->sessionId);
    }

    /**
     * @return $this
     */
    public function subscribes()
    {
        $this->topic->pushCriteria(new Subscribes($this->userId));
        return $this->load($this->userId, $this->sessionId);
    }

    /**
     * @param string $name
     * @return HomeController
     */
    public function tag($name)
    {
        $this->topic->pushCriteria(new WithTag($name));
        return $this->load($this->userId, $this->sessionId);
    }

    /**
     * Mark ALL categories as READ
     */
    public function mark()
    {
        $forums = $this->forum->all(['id']);
        foreach ($forums as $forum) {
            $this->forum->markAsRead($forum->id, $this->userId, request()->session()->getId());
        }
    }
}
