<?php
namespace Coyote\Http\Controllers;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Services\Breadcrumbs;
use Coyote\Services\Guest;
use Coyote\User;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, CacheFactory;

    protected Breadcrumbs $breadcrumb;
    protected ?int $userId;
    protected ?User $auth;
    protected string $guestId;
    protected ?array $settings = null;
    protected Request $request;

    public function __construct()
    {
        $this->breadcrumb = new Breadcrumbs();
        $this->middleware(function (Request $request, $next) {
            $this->auth = $request->user();
            $this->userId = $request->user() ? $this->auth->id : null;
            $this->guestId = $request->session()->get('guest_id');
            $this->request = $request;
            return $next($request);
        });
    }

    /**
     * @param string|null $view
     * @param array $data
     * @return View
     */
    protected function view($view = null, $data = [])
    {
        if (!$this->request->ajax()) {
            $breadcrumbs = $this->breadcrumb->render();
            if ($breadcrumbs) {
                $data['breadcrumb'] = $breadcrumbs;
            }
        }
        return view($view, $data);
    }

    /**
     * @param string $name
     * @param $value
     * @return string
     */
    protected function setSetting(string $name, $value)
    {
        return app(Guest::class)->setSetting($name, $value);
    }

    /**
     * Get user's settings as array (setting => value)
     *
     * @return array|null
     */
    protected function getSettings()
    {
        /** @var Guest $app */
        $app = app(Guest::class);
        $settings = $app->getSettings();
        return [
            'colorScheme'     => $settings['colorScheme'] ?? 'system',
            'lastColorScheme' => $settings['lastColorScheme'] ?? 'light',
            'topicMode'       => ($settings['homepage.mode'] ?? 0) ? 'newest' : 'interesting',
            'reputationMode'  => ['month', 'year', 'total'][$settings['homepage.reputation'] ?? 0],
        ];
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    protected function getSetting($name, $default = null)
    {
        return app(Guest::class)->getSetting($name, $default);
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
