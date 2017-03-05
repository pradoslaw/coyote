<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Factories\GateFactory;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Breadcrumb\Breadcrumb;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, GateFactory, CacheFactory;

    /**
     * @var Breadcrumb
     */
    protected $breadcrumb;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var \Coyote\User
     */
    protected $auth;

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * Stores user's custom settings (like active tab or tags) from settings table
     *
     * @var array|null
     */
    protected $settings = null;

    /**
     * Public data that will be passed to JS as a JSON object
     *
     * @var array
     */
    protected $public = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->breadcrumb = new Breadcrumb();

        $this->middleware(function (Request $request, $next) {
            $this->auth = $request->user();
            $this->userId = $request->user() ? $this->auth->id : null;
            $this->sessionId = $request->session()->getId();

            $this->request = $request;

            $this->buildPublic();

            return $next($request);
        });
    }

    protected function buildPublic()
    {
        // URL to main page and CDN
        $this->public = array_merge($this->public, [
            'public'    => url()->route('home'),
            'cdn'       => config('app.cdn') ? ('//' . config('app.cdn')) : url()->route('home'),
            'ping'      => route('ping', [], false)
        ]);

        if ($this->userId && config('services.ws.host')) {
            $this->public['ws'] = config('services.ws.host') . (config('services.ws.port') ? ':' . config('services.ws.port') : '');
            // token contains channel name
            $this->public['token'] = app(Encrypter::class)->encrypt('user:' . $this->userId . '|' . time());
        }
    }

    /**
     * Application menu. Menu can be overwrite in admin panel.
     *
     * @return \Lavary\Menu\Builder
     */
    protected function buildMenu()
    {
        $menu = app(Menu::class)->make('master', function (Builder $menu) {
            foreach (config('laravel-menu.master') as $title => $data) {
                $children = array_pull($data, 'children');
                $item = $menu->add($title, $data);

                foreach ((array) $children as $key => $child) {
                    /** @var \Lavary\Menu\Item $item */
                    $item->add($key, $child);
                }
            }
        });

        if (!$this->userId || ($this->userId && $this->auth->created_at->diffInDays() <= 7)) {
            // tymczasowo wyswietlana ikona "Nowosc" przy ofertach pracy.
            $badge = app('html')->tag('span', 'Nowość', ['class' => 'badge new']);
            $menu->get('praca')->append($badge);
        }

        // cache user customized menu for 7 days
        $categories = $this->getCacheFactory()->tags('menu-for-user')->remember('menu-for-user:' . $this->userId, 60 * 24 * 7, function () {
            /** @var ForumRepositoryInterface $repository */
            $repository = app(ForumRepositoryInterface::class);
            // since repository is singleton, we have to reset previously set criteria to avoid duplicated them.
            $repository->resetCriteria();
            // make sure we don't skip criteria
            $repository->skipCriteria(false);

            $repository->pushCriteria(new OnlyThoseWithAccess($this->auth));
            $repository->pushCriteria(new AccordingToUserOrder($this->userId));
            $repository->applyCriteria();

            return $repository->select(['name', 'slug'])->whereNull('parent_id')->get()->toArray();
        });

        foreach ($categories as $forum) {
            /** @var array $forum */
            $menu->forum->add($forum['name'], route('forum.category', [$forum['slug']]));
        }

        return $menu;
    }

    /**
     * Renders view with breadcrumb
     *
     * @param string|null $view
     * @param array $data
     * @return \Illuminate\View\View
     */
    protected function view($view = null, $data = [])
    {
        if (!$this->request->ajax()) {
            // public JS variables
            $data['public'] = json_encode($this->public);

            if (count($this->breadcrumb)) {
                $data['breadcrumb'] = $this->breadcrumb->render();
            }

            $data['menu'] = $this->buildMenu();
        }

        return view($view, $data);
    }

    /**
     * @param string $name
     * @param $value
     */
    protected function setSetting($name, $value)
    {
        if ($this->getSetting($name) === $value) {
            return $value;
        }

        if (!is_array($this->settings)) {
            $this->settings = [];
        }

        app('setting')->setItem($name, $value, $this->userId, $this->sessionId);

        return $this->settings[$name] = $value;
    }

    /**
     * Get user's settings as array (setting => value)
     *
     * @return array|null
     */
    protected function getSettings()
    {
        if (is_null($this->settings)) {
            $this->settings = app('setting')->getAll($this->userId, $this->sessionId);
        }

        return $this->settings;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    protected function getSetting($name, $default = null)
    {
        return isset($this->getSettings()[$name]) ? $this->settings[$name] : $default;
    }

    /**
     * @param $formClass
     * @param mixed $data
     * @param array $options
     * @return \Coyote\Services\FormBuilder\Form
     */
    protected function createForm($formClass, $data = null, array $options = [])
    {
        return app('form.builder')->createForm($formClass, $data, $options);
    }

    /**
     * @return \Boduch\Grid\GridBuilder
     */
    protected function gridBuilder()
    {
        return app('grid.builder');
    }

    /**
     * @param \Closure $callback
     * @return mixed
     */
    protected function transaction(\Closure $callback)
    {
        return app(Connection::class)->transaction($callback);
    }
}
