<?php
namespace Coyote\Providers;

use Coyote\Domain\User\UserSettings;
use Coyote\Http\Composers\InitialStateComposer;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Services\Forum\UserDefined;
use Coyote\Services\Guest;
use Coyote\User;
use Coyote\View\Twig\TwigLiteral;
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
        $cache = app(Cache\Repository::class);
        /** @var Factory $view */
        $view = $this->app['view'];
        $view->composer(['layout', 'adm.home'], InitialStateComposer::class);
        $view->composer('layout', function (View $view) use ($cache) {
            $view->with([
                '__master_menu' => $this->buildMasterMenu(),

                // temporary code
                '__dark_theme'  => $this->app[Guest::class]->getSetting('dark.theme', true),
                'github_stars'  => $cache->remember('homepage:github_stars', 30 * 60, fn() => $this->githubStars()),

                'gdpr' => [
                    'content'  => TwigLiteral::fromHtml((new UserSettings)->cookieAgreement()),
                    'accepted' => $this->gdprAccepted(),
                ],
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
        $previousName = null;
        $sections = [];
        foreach ($categories as $category) {
            if ($previousName === null || (($category['section'] !== $previousName) && $category['section'])) {
                $previousName = $category['section'];
            }
            $sections[$previousName][] = $category;
        }
        return $sections;
    }

    private function githubStars(): ?int
    {
        $result = @\file_get_contents(
            'https://api.github.com/repos/pradoslaw/coyote',
            false,
            \stream_context_create([
                'http' => ['header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'],
            ]));
        if ($result !== false) {
            $data = @\json_decode($result, true);
            if ($data !== null) {
                return $data['stargazers_count'];
            }
        }
        return null;
    }
}
