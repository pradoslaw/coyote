<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Factories\GateFactory;
use Coyote\Services\Breadcrumb\Breadcrumb;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Lavary\Menu\Menu;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests, GateFactory;

    /**
     * @var Breadcrumb
     */
    protected $breadcrumb;

    /**
     * @var int
     */
    protected $userId;

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
     * Controller constructor.
     */
    public function __construct()
    {
        $this->breadcrumb = new Breadcrumb();
        $this->userId = auth()->check() ? auth()->user()->id : null;
        $this->sessionId = request()->session()->getId();

        // URL to main page and CDN
        $this->public = ['public' => url()->route('home'), 'cdn' => config('cdn', url()->route('home'))];

        if ($this->userId) {
            if (config('services.ws.host')) {
                $this->public['ws'] = config('services.ws.host') . ':' . config('services.ws.port');
                // token contains channel name
                $this->public['token'] = app('encrypter')->encrypt('user:' . $this->userId . '|' . time());
            }
        }
    }

    /**
     * Application menu. Menu can be overwrite in admin panel.
     *
     * @return Menu
     */
    protected function menu()
    {
        return app('menu')->make('main', function ($menu) {
            $menu->add('Forum', ['route' => 'forum.home'])->active('Forum/*');
            $menu->add('Mikroblogi', ['route' => 'microblog.home'])->active('Mikroblogi/*');
            $menu->add('Praca', ['route' => 'job.home'])->active('Praca/*');
            $menu->add('Pastebin', ['route' => 'pastebin.home'])->active('Pastebin/*');
        });
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
        // public JS variables
        $data['public'] = json_encode($this->public);

        if (count($this->breadcrumb)) {
            $data['breadcrumb'] = $this->breadcrumb->render();
        }

        if (!request()->ajax()) {
            $data['menu'] = $this->menu();
        }

        return view($view, $data);
    }

    /**
     * @param string $name
     * @param $value
     */
    protected function setSetting($name, $value)
    {
        app('setting')->setItem($name, $value, $this->userId, $this->sessionId);

        if (!is_array($this->settings)) {
            $this->settings;
        }

        $this->settings[$name] = $value;
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
}
