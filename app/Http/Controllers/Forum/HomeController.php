<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Factories\GateFactory;
use Coyote\Http\Resources\ForumCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\SkipForum;
use Coyote\Repositories\Criteria\Topic\SkipLockedCategories;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Topic\WithTags;
use Coyote\Services\Forum\Personalizer;
use Illuminate\Http\Request;
use Lavary\Menu\Item;
use Lavary\Menu\Menu;
use Lavary\Menu\Builder;

class HomeController extends BaseController
{
    use GateFactory, FlagFactory;

    /**
     * @var Builder
     */
    private $tabs;

    /**
     * @var Personalizer
     *
     * @deprecated
     */
    private $personalizer;

    /**
     * @param ForumRepository $forum
     * @param TopicRepository $topic
     * @param PostRepository $post
     * @param Personalizer $personalizer
     */
    public function __construct(
        ForumRepository $forum,
        TopicRepository $topic,
        PostRepository $post,
        Personalizer $personalizer
    ) {
        parent::__construct($forum, $topic, $post);

        $this->personalizer = $personalizer;

        $this->tabs = app(Menu::class)->make('_forum', function (Builder $menu) {
            foreach (config('laravel-menu._forum') as $title => $row) {
                $data = array_pull($row, 'data');
                $menu->add($title, $row)->data($data);
            }
        });

        $this->middleware(function (Request $request, $next) {
            $this->tabs->filter(function (Item $item) {
                if ($item->data('role') === true) {
                    return $this->userId !== null;
                }

                return true;
            });

            // currently selected tab
            list(, $suffix) = explode('.', $request->route()->getName());

            if (in_array($suffix, ['categories', 'all', 'subscribes', 'mine', 'interesting'])) {
                $this->setSetting('forum.tab', $suffix);
            }

            return $next($request);
        });
    }

    /**
     * @param string $view
     * @param array $data
     * @return \Illuminate\View\View
     */
    protected function view($view = null, $data = [])
    {
        list(, $suffix) = explode('.', $this->request->route()->getName());

        $currentTab = $suffix == 'home' ? $this->getSetting('forum.tab', 'categories') : $suffix;
        $title = null;

        foreach ($this->tabs->all() as $tab) {
            if ("forum.$currentTab" == $tab->link->path['route']) {
                $tab->activate();

                $title = $tab->title;
            }
        }

        return parent::view($view, $data)->with(['tabs' => $this->tabs, 'title' => $title]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tab = $this->getSetting('forum.tab', 'categories');

        return $this->{$tab}();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app('parser.post');
        $parser->cache->setEnable(false);

        return response($parser->parse((string) $request->get('text')));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        start_measure('foo');
        // execute query: get all categories that user can has access
        $forums = $this->withCriteria(function () {
            return $this
                ->forum
                ->categories($this->guestId)
                ->mapCategory($this->guestId);
        });

        $forums = new ForumCollection($forums);
stop_measure('foo');
        return $this->view('forum.home')->with(compact('forums'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function all()
    {
        $this->topic->pushCriteria(new SkipLockedCategories());

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

        $user = app(UserRepositoryInterface::class)->find($userId);
        abort_if(is_null($user), 404);

        if ($this->request->route()->getName() == 'forum.user') {
            $this
                ->tabs
                ->add('Posty: ' . $user->name, [
                    'route' => [
                        'forum.user', $userId
                    ]
                ])
                ->activate();
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
        $this
            ->tabs
            ->add('Wątki z: ' . $this->request->route('tag'), [
                'route' => [
                    'forum.tag', urlencode($this->request->route('tag'))
                ]
            ])
            ->activate();

        $this->topic->pushCriteria(new WithTags($name));
        return $this->loadAndRender();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function interesting()
    {
        $this->topic->pushCriteria(new WithTags(json_decode($this->getSetting('forum.tags', '[]'))));

        return $this->loadAndRender();
    }

    /**
     * Mark ALL categories as READ
     */
    public function mark()
    {
        $forums = $this->forum->all(['id']);

        /** @var \Coyote\Forum $forum */
        foreach ($forums as $forum) {
            $forum->markAsRead($this->guestId);
            $this->topic->flushRead($forum->id, $this->guestId);
        }
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function load()
    {
        $this->topic->pushCriteria(new OnlyThoseWithAccess($this->auth));

        // if someone wants to find all user's topics, we can't hide those from our hidden categories.
        if (strpos($this->request->route()->getActionName(), '@user') === false) {
            $this->topic->pushCriteria(new SkipForum($this->forum->findHiddenIds($this->userId)));
        }

        $paginator = $this
            ->topic
            ->lengthAwarePagination(
                $this->userId,
                $this->guestId,
                'topics.last_post_id',
                'DESC',
                $this->topicsPerPage($this->request)
            )
            ->appends($this->request->except('page'));

        return $this->personalizer->markUnreadTopics($paginator);
    }

    /**
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $topics
     * @return \Illuminate\View\View
     */
    private function render($topics)
    {
        $flags = [];

        // we need to get an information about flagged topics. that's how moderators can notice
        // that's something's wrong with posts.
        if ($topics->total() && $this->getGateFactory()->allows('forum-delete')) {
            $flags = $this->getFlagFactory()->takeForTopics($topics->groupBy('id')->keys()->toArray());
        }

        $postsPerPage = $this->postsPerPage($this->request);

        return $this->view('forum.topics')->with(compact('topics', 'flags', 'postsPerPage'));
    }

    /**
     * @return \Illuminate\View\View
     */
    private function loadAndRender()
    {
        return $this->render($this->load());
    }
}
