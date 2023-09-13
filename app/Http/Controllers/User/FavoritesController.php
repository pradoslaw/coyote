<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Job;
use Coyote\Microblog;
use Coyote\Models\Subscription;
use Coyote\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
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

    public function index(): RedirectResponse
    {
        return redirect()->action('User\FavoritesController@forum');
    }

    public function forum(): View
    {
        $this->breadcrumb->push('Wątki na forum', route('user.favorites.forum'));
        return $this->load(Topic::class);
    }

    public function job(): View
    {
        $this->breadcrumb->push('Oferty pracy', route('user.favorites.job'));
        return $this->load(Job::class);
    }

    public function microblog(): View
    {
        $this->breadcrumb->push('Mikroblogi', route('user.favorites.microblog'));
        return $this->load(Microblog::class);
    }

    protected function load(string $resource): View
    {
        $subscriptions = Subscription::where('user_id', $this->userId)
          ->whereHasMorph('resource', [$resource])
          ->with('resource')
          ->orderBy('id', 'DESC')
          ->paginate();

        return $this->view(
          'user.favorites',
          [
            'tabs'          => $this->getTabs(),
            'partial'       => $this->request->route()->getName(),
            'subscriptions' => $subscriptions,
            'paginate'      => $subscriptions->links()
          ]
        );
    }

    protected function getTabs(): Builder
    {
        /** @var Menu $menu */
        $menu = app(Menu::class);
        return $menu->make('favorites', function (Builder $menu) {
            $tabs = [
              'user.favorites.forum'     => 'Wątki na forum',
              'user.favorites.job'       => 'Oferty pracy',
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
