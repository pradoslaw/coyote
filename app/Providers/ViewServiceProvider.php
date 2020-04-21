<?php

namespace Coyote\Providers;

use Coyote\Http\Composers\InitialStateComposer;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Services\Guest;
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
        $this->app['view']->composer(['layout', 'adm.home'], InitialStateComposer::class);

        $this->app['view']->composer('layout', function (View $view) {
            $view->with([
                '__master_menu' => $this->buildMasterMenu(),

                // temporary code
                '__dark_theme' => $this->app[Guest::class]->getSetting('dark.theme', false)
            ]);
        });
    }

    private function buildMasterMenu()
    {
        $userId = $this->app['request']->user() ? $this->app['request']->user()->id : null;

        // cache user customized menu for 7 days
        /** @var \Lavary\Menu\Builder $builder */
        $builder = $this->getCacheFactory()->tags('menu-for-user')->remember('menu-for-user:' . $userId, 60 * 24 * 7, function () use ($userId) {
            $builder = app(Menu::class)->make('__master_menu___', function (Builder $menu) {
                foreach (config('laravel-menu.__master_menu___') as $title => $data) {
                    $children = array_pull($data, 'children');
                    $item = $menu->add($title, $data);

                    foreach ((array) $children as $key => $child) {
                        /** @var \Lavary\Menu\Item $item */
                        $item->add($key, $child);
                    }
                }
            });

            /** @var ForumRepositoryInterface $repository */
            $repository = app(ForumRepositoryInterface::class);
            // since repository is singleton, we have to reset previously set criteria to avoid duplicated them.
            $repository->resetCriteria();
            // make sure we don't skip criteria
            $repository->skipCriteria(false);

            $repository->pushCriteria(new OnlyThoseWithAccess($this->app['request']->user()));
            $repository->pushCriteria(new AccordingToUserOrder($userId, true));
            $repository->applyCriteria();

            $categories = $repository->addSelect(['name', 'slug', 'forums.section'])->whereNull('parent_id')->get();
            $rendered = view('components.mega-menu', ['sections' => $this->groupBySections($categories)])->render();

            $builder->forum->after($rendered);

            return $builder;
        });

        // ugly hack for laravel menu: remove cached "active" class from item's attribute.
        if (true === $builder->conf('auto_activate')) {
            foreach ($builder->all() as $item) {
                /** @var \Lavary\Menu\Item $item */
                $item->isActive = false;
                $item->attr('class', '');

                $item->checkActivationStatus();
            }
        }

        return $builder;
    }

    public function groupBySections($categories)
    {
        $name = null;
        $sections = [];

        foreach ($categories as $category) {
            if ($name === null || ($category->section !== $name && $category->section)) {
                $name = $category->section;
            }

            if (!isset($sections[$name])) {
                $sections[$name] = [];
            }

            array_push($sections[$name], $category);
        }

        return $sections;
    }
}
