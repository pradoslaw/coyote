<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\RenderParams;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\ForumCollection;
use Coyote\Http\Resources\TopicCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Topic\SkipForum;
use Coyote\Repositories\Criteria\Topic\SkipLockedCategories;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\WithTags;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Flags;
use Coyote\Services\Guest;
use Coyote\Topic;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Lavary\Menu\Builder;
use Lavary\Menu\Item;
use Lavary\Menu\Menu;

class HomeController extends BaseController
{
    private ?Builder $tabs;

    /**
     * @var TagRepository
     */
    protected $tag;

    /**
     * @param ForumRepository $forum
     * @param TopicRepository $topic
     * @param PostRepository $post
     * @param TagRepository $tag
     */
    public function __construct(
        ForumRepository $forum,
        TopicRepository $topic,
        PostRepository  $post,
        TagRepository   $tag,
    )
    {
        parent::__construct($forum, $topic, $post, $tag);

        /** @var Menu $app */
        $app = app(Menu::class);
        $this->tabs = $app->make('_forum', function (Builder $menu) {
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
            [, $suffix] = explode('.', $request->route()->getName());

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
        [, $suffix] = explode('.', $this->request->route()->getName());

        $currentTab = $suffix == 'home' ? $this->getSetting('forum.tab', 'categories') : $suffix;
        $title = null;

        foreach ($this->tabs->all() as $tab) {
            if ("forum.$currentTab" == $tab->link->path['route']) {
                $tab->activate();

                $title = $tab->title;
            }
        }

        return parent::view($view, $data)->with(['tabs' => $this->tabs, 'title' => $currentTab != 'categories' ? $title : '']);
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
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        // execute query: get all categories that user can has access
        $this->pushForumCriteria();

        $forums = $this
            ->forum
            ->categories($this->guestId)
            ->mapCategory();

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
        if ($this->userId) {
            $this->topic->pushCriteria(new OnlyMine($this->userId, false));
        }

        $topics = $this->load();

        return $this->render($topics)->with('user_id', $this->userId);
    }

    /**
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function user(int $userId)
    {
        $this->topic->pushCriteria(new OnlyMine($userId, true));
        $topics = $this->load();

        $user = app(UserRepositoryInterface::class)->pushCriteria(new WithTrashed())->find($userId);
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

    public function tag(string $name): View
    {
        $item = $this
            ->tabs
            ->add('Wątki z: ' . $name, [
                'route' => [
                    'forum.tag', urlencode($this->request->route('tag')),
                ],
                'class' => 'nav-item',
            ]);

        $item->link->attr(['class' => 'nav-link']);
        $item->activate();

        $this->topic->pushCriteria(new WithTags($name));

        return $this->loadAndRender(new RenderParams($name));
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
                $this->topicsPerPage($this->request),
            )
            ->appends($this->request->except('page'));
    }

    /**
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginate
     * @return \Illuminate\View\View
     */
    private function render($paginate, RenderParams $renderParams = null)
    {
        $guest = new Guest($this->guestId);

        $topics = (new TopicCollection($paginate))
            ->setGuest($guest)
            ->setRepository($this->topic);

        $flags = resolve(Flags::class)->fromModels([Topic::class])->permission('forum-delete')->get();
        $flags = FlagResource::collection($flags);

        $postsPerPage = $this->postsPerPage($this->request);

        return $this->view('forum.topics')
            ->with(compact('topics', 'flags', 'postsPerPage'))
            ->with(compact('renderParams', $renderParams));
    }

    /**
     * @return \Illuminate\View\View
     */
    private function loadAndRender(RenderParams $renderParams = null)
    {
        return $this->render($this->load(), $renderParams);
    }
}
