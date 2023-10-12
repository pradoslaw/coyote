<?php
namespace Coyote\Providers;

use Coyote\Http\Composers\InitialStateComposer;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Services\Forum\UserDefined;
use Coyote\Services\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class ViewServiceProvider extends ServiceProvider
{
    use CacheFactory;

    public function boot(): void
    {
        $this->app['view']->composer(['layout', 'adm.home'], InitialStateComposer::class);

        $this->app['view']->composer('layout', function (View $view) {
            $view->with([
                '__master_menu' => $this->buildMasterMenu(),

                // temporary code
                '__dark_theme'  => $this->app[Guest::class]->getSetting('dark.theme', true)
            ]);
        });
    }

    private function buildMasterMenu(): Builder
    {
        /** @var Menu $menu */
        $menu = app(Menu::class);
        /** @var Builder $builder */
        $builder = $menu->make('__master_menu___', function (Builder $menu) {
            foreach (config('laravel-menu.__master_menu___') as $title => $data) {
                $children = array_pull($data, 'children');
                $item = $menu->add($title, $data);
                foreach ((array)$children as $key => $child) {
                    $item->add($key, $child);
                }
            }
        });

        $categories = collect($this->app[UserDefined::class]->allowedForums($this->app['request']->user()))->where('parent_id', null);
        $rendered = view('components.mega-menu', ['sections' => $this->groupBySections($categories)])->render();

        $builder->forum->after($rendered);

        return $builder;
    }

    public function groupBySections($categories): array
    {
        $name = null;
        $sections = [];

        foreach ($categories as $category) {
            if ($name === null || ($category['section'] !== $name && $category['section'])) {
                $name = $category['section'];
            }

            if (!isset($sections[$name])) {
                $sections[$name] = [];
            }

            array_push($sections[$name], $category);
        }

        return $sections;
    }
}
