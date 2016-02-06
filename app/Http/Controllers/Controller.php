<?php namespace Coyote\Http\Controllers;

use Coyote;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Coyote\Breadcrumb
     */
    protected $breadcrumb;

    /**
     * Stores user's custom settings (like active tab or tags) from settings table
     *
     * @var array|null
     */
    protected $settings;

    public function __construct()
    {
        $this->breadcrumb = new Coyote\Breadcrumb();
    }

    /**
     * Renders view with breadcrumb
     *
     * @param null $view
     * @param array $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function view($view = null, $data = [])
    {
        if (count($this->breadcrumb)) {
            $data['breadcrumb'] = $this->breadcrumb->render();
        }

        return view($view, $data);
    }

    /**
     * @param $name
     * @param $value
     */
    protected function setSetting($name, $value)
    {
        app()->make('setting')->setItem($name, $value, auth()->id(), request()->session()->getId());

        if (is_array($this->settings)) {
            $this->settings[$name] = $value;
        }
    }

    /**
     * Get user's settings as array (setting => value)
     *
     * @return array|null
     */
    protected function getSettings()
    {
        if (is_null($this->settings)) {
            $this->settings = app()->make('setting')->getAll(auth()->id(), request()->session()->getId());
        }

        return $this->settings;
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    protected function getSetting($name, $default = null)
    {
        return isset($this->getSettings()[$name]) ? $this->settings[$name] : $default;
    }
}
