<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Factories\FlagFactory;
use Coyote\Http\Factories\GateFactory;
use Coyote\Http\Resources\Api\ForumCollection;
use Coyote\Http\Resources\TopicCollection;
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
use Coyote\Services\Guest;
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
     * @param ForumRepository $forum
     * @param TopicRepository $topic
     * @param PostRepository $post
     */
    public function __construct(
        ForumRepository $forum,
        TopicRepository $topic,
        PostRepository $post
    ) {
        parent::__construct($forum, $topic, $post);

        $this->tabs = app(Menu::class)->make('_forum', function (Builder $menu) {
            foreach (config('laravel-menu._forum') as $title => $row) {
                $data = array_pull($row, 'data');
                $item = $menu->add($title, $row);

                $item->link->attr(['class' => 'nav-link']);
                $item->data($data);
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
        // execute query: get all categories that user can has access
        $forums = $this->withCriteria(function () {
            return $this
                ->forum
                ->categories($this->guestId)
                ->mapCategory();
        });

        $forums = ForumCollection::factory($forums);
        $collapse = $this->collapse();

        return $this->view('forum.home')->with(compact('forums', 'collapse'));
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
        $this->topic->pushCriteria(new OnlyMine($this->userId, false));
        $topics = $this->load();

        return $this->render($topics)->with('user_id', $this->userId);
    }

    /**
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function user($userId)
    {
        $this->topic->pushCriteria(new OnlyMine($userId, true));
        $topics = $this->load();

        $user = app(UserRepositoryInterface::class)->find($userId);
        abort_if(is_null($user), 404);

        if ($this->request->route()->getName() == 'forum.user') {
            $item = $this->tabs->add('Posty: ' . $user->name, ['route' => ['forum.user', $userId], 'class' => 'nav-item']);

            $item->link->attr(['class' => 'nav-link']);
            $item->activate();
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
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function load()
    {
        $this->topic->pushCriteria(new OnlyThoseWithAccess($this->auth));

        // if someone wants to find all user's topics, we can't hide those from our hidden categories.
        if (strpos($this->request->route()->getActionName(), '@user') === false) {
            $this->topic->pushCriteria(new SkipForum($this->forum->findHiddenIds($this->userId)));
        }

        return $this
            ->topic
            ->lengthAwarePagination(
                $this->userId,
                $this->guestId,
                'topics.last_post_id',
                'DESC',
                $this->topicsPerPage($this->request)
            )
            ->appends($this->request->except('page'));
    }

    /**
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginate
     * @return \Illuminate\View\View
     */
    private function render($paginate)
    {
        $guest = new Guest($this->guestId);

        $topics = (new TopicCollection($paginate))
            ->setGuest($guest)
            ->setRepository($this->topic);

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
