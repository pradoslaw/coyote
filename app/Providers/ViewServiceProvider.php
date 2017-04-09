<?php

namespace Coyote\Providers;

use Coyote\Http\Factories\CacheFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class ViewServiceProvider extends ServiceProvider
{
    use CacheFactory;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['view']->composer('layout', function (View $view) {
            $this->registerPublicData();
            $this->registerWebSocket();

            $this->buildMasterMenu();

            $view->with([
                '__public' => json_encode($this->app['request']->attributes->all()),
                '__master_menu' => $this->buildMasterMenu()
            ]);
        });
    }

    private function registerWebSocket()
    {
        if (config('services.ws.host') && $this->app['request']->user()) {
            $this->app['request']->attributes->set(
                'ws',
                config('services.ws.host') . (config('services.ws.port') ? ':' . config('services.ws.port') : '')
            );
        }
    }

    private function registerPublicData()
    {
        $this->app['request']->attributes->add([
            'public'        => route('home'),
            'cdn'           => config('app.cdn') ? ('//' . config('app.cdn')) : route('home'),
            'ping'          => route('ping', [], false),
            'ping_interval' => config('session.lifetime') - 5 // every 10 minutes
        ]);
    }

    private function buildMasterMenu()
    {
        $builder = app(Menu::class)->make('__master_menu___', function (Builder $menu) {
            foreach (config('laravel-menu.master') as $title => $data) {
                $children = array_pull($data, 'children');
                $item = $menu->add($title, $data);

                foreach ((array) $children as $key => $child) {
                    /** @var \Lavary\Menu\Item $item */
                    $item->add($key, $child);
                }
            }
        });

        $userId = $this->app['request']->user() ? $this->app['request']->user()->id : null;

        // cache user customized menu for 7 days
        $categories = $this->getCacheFactory()->tags('menu-for-user')->remember('menu-for-user:' . $userId, 60 * 24 * 7, function () use ($userId) {
            /** @var ForumRepositoryInterface $repository */
            $repository = app(ForumRepositoryInterface::class);
            // since repository is singleton, we have to reset previously set criteria to avoid duplicated them.
            $repository->resetCriteria();
            // make sure we don't skip criteria
            $repository->skipCriteria(false);

            $repository->pushCriteria(new OnlyThoseWithAccess($this->app['request']->user()));
            $repository->pushCriteria(new AccordingToUserOrder($userId));
            $repository->applyCriteria();

            return $repository->select(['name', 'slug'])->whereNull('parent_id')->get()->toArray();
        });

        foreach ($categories as $forum) {
            /** @var array $forum */
            $builder->forum->add($forum['name'], route('forum.category', [$forum['slug']]));
        }

        return $builder;
    }
}
