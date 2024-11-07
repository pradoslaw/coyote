<?php
namespace Coyote\Providers;

use Coyote\Domain\Clock;
use Coyote\Domain\Icon\Icons;
use Coyote\Domain\User\UserSettings;
use Coyote\Http\Composers\InitialStateComposer;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Services\Forum\UserDefined;
use Coyote\Services\Guest;
use Coyote\User;
use Illuminate\Contracts\Cache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class ViewServiceProvider extends ServiceProvider
{
    use CacheFactory;

    public function boot(): void
    {
        /** @var Cache\Repository $cache */
        $cache = app(Cache\Repository::class);
        /** @var Clock $clock */
        $clock = app(Clock::class);
        /** @var Factory $view */
        $view = $this->app['view'];
        $view->composer(['layout', 'adm.home'], InitialStateComposer::class);
        $view->composer(['layout', 'adm.home'], function (View $view) {
            $view->with(['darkTheme' => $this->initialDarkTheme()]);
        });
        $view->composer('layout', function (View $view) use ($clock, $cache) {
            $view->with([
                '__master_menu'  => $this->buildMasterMenu(),
                '__dark_theme'   => $this->initialDarkTheme(),
                '__color_scheme' => $this->colorScheme(),
                'gdpr'           => [
                    'content'  => (new UserSettings)->cookieAgreement(),
                    'accepted' => $this->gdprAccepted(),
                ],
                'year'           => $clock->year(),
                'currentUser'    => $this->currentUser(),
                'icons'          => (new Icons)->icons(),
            ]);
        });
    }

    private function gdprAccepted(): bool
    {
        /** @var Request $request */
        $request = $this->app['request'];
        $user = $request->user();
        if ($user) {
            /** @var User $user */
            return (bool)$user->gdpr;
        }
        return false;
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

    public function groupBySections(Support\Collection $categories): array
    {
        $sections = [];
        foreach ($categories as $category) {
            if ($category['section'] === null) {
                continue;
            }
            $sections[$category['section']][] = $category;
        }
        return $sections;
    }

    private function initialDarkTheme(): bool
    {
        /** @var Guest $guest */
        $guest = $this->app[Guest::class];
        return $guest->getSetting('lastColorScheme',
                $this->legacyLastColorScheme()) === 'dark';
    }

    private function legacyLastColorScheme(): ?string
    {
        /** @var Guest $guest */
        $guest = $this->app[Guest::class];
        return $guest->getSetting('dark.theme', true) ? 'dark' : 'light';
    }

    private function colorScheme(): ?string
    {
        /** @var Guest $guest */
        $guest = $this->app[Guest::class];
        $colorScheme = $guest->getSetting('colorScheme');
        if ($colorScheme !== null) {
            return $colorScheme;
        }
        $legacyDarkTheme = $guest->getSetting('dark.theme');
        if ($legacyDarkTheme === null) {
            return 'system';
        }
        return $legacyDarkTheme ? 'dark' : 'light';
    }

    function currentUser(): ?array
    {
        if (auth()->guest()) {
            return null;
        }
        /** @var User $user */
        $user = auth()->user();
        return [
            'photo' => $user->photo,
            'name'  => $user->name,
        ];
    }
}
