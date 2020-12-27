<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Job;
use Coyote\Microblog;
use Coyote\Models\Subscription;
use Coyote\Topic;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class FavoritesController extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumb->push('Ulubione i obserwowane strony', route('user.favorites'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->action('User\FavoritesController@forum');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function forum()
    {
        $this->breadcrumb->push('Wątki na forum', route('user.favorites.forum'));

        return $this->load(Topic::class);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function job()
    {
        $this->breadcrumb->push('Oferty pracy', route('user.favorites.job'));

        return $this->load(Job::class);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function microblog()
    {
        $this->breadcrumb->push('Mikroblogi', route('user.favorites.microblog'));

        return $this->load(Microblog::class);
    }

    /**
     * @param string $resource
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function load(string $resource)
    {
        $subscriptions = Subscription::where('user_id', $this->userId)
            ->whereHasMorph('resource', [$resource])
            ->with('resource')
            ->orderBy('id', 'DESC')
            ->paginate();

        return $this->view(
            'user.favorites',
            [
                'tabs' => $this->getTabs(),
                'partial' => $this->request->route()->getName(),
                'subscriptions' => $subscriptions,
                'paginate' => $subscriptions->links()
            ]
        );
    }

    /**
     * @return mixed
     */
    protected function getTabs()
    {
        return app(Menu::class)->make('favorites', function (Builder $menu) {
            $tabs = [
                'user.favorites.forum' => 'Wątki na forum',
                'user.favorites.job' => 'Oferty pracy',
                'user.favorites.microblog' => 'Mikroblogi'
            ];

            foreach ($tabs as $route => $label) {
                $item = $menu->add("<span>$label</span>", ['route' => $route]);
                $item->link->attr(['class' => 'nav-item']);

                if ($route === request()->route()->getName()) {
                    $item->link->active();
                }
            }
        });
    }
}
