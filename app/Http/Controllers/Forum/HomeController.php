<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Factories\GateFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\Topic\Unanswered;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Topic\WithTag;
use Illuminate\Http\Request;
use Lavary\Menu\Menu;

class HomeController extends BaseController
{
    use GateFactory, FlagFactory;

    /**
     * @param string $view
     * @param array $data
     * @return \Illuminate\View\View
     */
    protected function view($view = null, $data = [])
    {
        $route = $this->getRouter()->currentRouteName();
        $request = $this->getRouter()->getCurrentRequest();

        $tabs = app(Menu::class)->make('tabs', function ($menu) {
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
            $tabs->add('Wątki z: ' . $request->route('tag'), [
                'route' => [
                    'forum.tag', urlencode($request->route('tag'))
                ]
            ]);
        }

        if ($route == 'forum.user') {
            $user = app(UserRepositoryInterface::class)->find($request->route('id'));
            abort_if(is_null($user), 404);

            $tabs->add('Posty: ' . $user->name, [
                'route' => [
                    'forum.user', $request->route('id')
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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->pushForumCriteria();
        // execute query: get all categories that user can has access
        $sections = $this->forum->groupBySections($this->userId, $this->sessionId);
        // get categories collapse
        $collapse = $this->collapse();

        return $this->view('forum.home')->with(compact('sections', 'collapse'));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app('parser.post');
        $parser->cache->setEnable(false);

        return response($parser->parse($request->get('text')));
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function load()
    {
        $this->topic->pushCriteria(new OnlyThoseWithAccess(auth()->user()));

        return $this
            ->topic
            ->paginate(
                $this->userId,
                $this->sessionId,
                'topics.last_post_id',
                'DESC',
                $this->topicsPerPage($this->getRouter()->getCurrentRequest())
            )
            ->appends(request()->except('page'));
    }

    /**
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $topics
     * @return \Illuminate\View\View
     */
    private function render($topics)
    {
        // we need to get an information about flagged topics. that's how moderators can notice
        // that's something's wrong with posts.
        if ($topics->total() && $this->getGateFactory()->allows('forum-delete')) {
            $flags = $this->getFlagFactory()->takeForTopics($topics->groupBy('id')->keys()->toArray());
        }

        $postsPerPage = $this->postsPerPage($this->getRouter()->getCurrentRequest());

        return $this->view('forum.topics')->with(compact('topics', 'flags', 'postsPerPage'));
    }

    /**
     * @return \Illuminate\View\View
     */
    private function loadAndRender()
    {
        return $this->render($this->load());
    }

    /**
     * @return \Illuminate\View\View
     */
    public function all()
    {
        return $this->loadAndRender();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function unanswered()
    {
        $this->topic->pushCriteria(new Unanswered());
        return $this->loadAndRender();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function mine()
    {
        return $this->user($this->userId);
    }

    /**
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function user($userId)
    {
        $this->topic->pushCriteria(new OnlyMine($userId));
        $topics = $this->load();

        if ($topics->total() > 0) {
            $topics->load(['posts' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }]);
        }

        return $this->render($topics)->with('user_id', $userId);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function subscribes()
    {
        $this->topic->pushCriteria(new Subscribes($this->userId));
        return $this->loadAndRender();
    }

    /**
     * @param string $name
     * @return \Illuminate\View\View
     */
    public function tag($name)
    {
        $this->topic->pushCriteria(new WithTag($name));
        return $this->loadAndRender();
    }

    /**
     * Mark ALL categories as READ
     */
    public function mark()
    {
        $forums = $this->forum->all(['id']);
        foreach ($forums as $forum) {
            $this->forum->markAsRead($forum->id, $this->userId, $this->sessionId);
        }
    }
}
