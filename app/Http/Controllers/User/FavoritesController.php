<?php

namespace Coyote\Http\Controllers\User;

use Lavary\Menu\Menu;

class FavoritesController extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumb->push('Ulubione i obserwowane strony');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function forum()
    {
        return $this->view('user.favorites');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function job()
    {
        return $this->view('user.favorites');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function microblog()
    {
        return $this->view('user.favorites');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function wiki()
    {
        return $this->view('user.favorites');
    }

    /**
     * @param null $view
     * @param array $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function view($view = null, $data = [])
    {
        return parent::view($view, $data + ['tabs' => $this->getTabs()]);
    }

    protected function getTabs()
    {
        return app(Menu::class)->make('favorites', function ($menu) {
            $menu->add('Wątki na forum', ['route' => 'user.favorites.forum'])->activate();
            $menu->add('Oferty pracy', ['route' => 'user.favorites.job', 'as' => 'job']);
            $menu->add('Mikroblogi', ['route' => 'user.favorites.microblog', 'as' => 'microblog']);
            $menu->add('Wiki', ['route' => 'user.favorites.wiki', 'as' => 'wiki']);
        });
    }
}
