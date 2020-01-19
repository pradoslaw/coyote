<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Factories\GateFactory;
use Coyote\Services\Breadcrumb\Breadcrumb;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
    protected $guestId;

    /**
     * Stores user's custom settings (like active tab or tags) from settings table
     *
     * @var array|null
     */
    protected $settings = null;

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
            $this->guestId = $request->session()->get('guest_id');

            $this->request = $request;

            return $next($request);
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
        if (!$this->request->ajax()) {
            if (count($this->breadcrumb)) {
                $data['breadcrumb'] = $this->breadcrumb->render();
            }
        }

        return view($view, $data);
    }

    /**
     * @param string $name
     * @param $value
     * @return string
     */
    protected function setSetting($name, $value)
    {
        if ($this->getSetting($name) === $value) {
            return $value;
        }

        if (!is_array($this->settings)) {
            $this->settings = [];
        }

        app('setting')->setSetting($name, $value, $this->guestId);

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
            $this->settings = app('setting')->getSettings($this->guestId);
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
