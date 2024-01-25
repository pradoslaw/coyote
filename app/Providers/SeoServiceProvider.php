<?php
namespace Coyote\Providers;

use Coyote\Domain\Seo\Schema;
use Coyote\View\Twig\TwigLiteral;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class SeoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];
        $view->composer('layout', function (View $view): void {
            $view->with([
                'schema_organization' => TwigLiteral::fromHtml(new Schema(new Schema\Organization())),
                'meta_robots'         => $this->metaRobots(),
                'meta_canonical'      => $this->metaCanonical(),
            ]);
        });
    }

    private function metaRobots(): string
    {
        /** @var Request $request */
        $request = $this->app['request'];
        if ($request->getHost() === '4programmers.dev') {
            return 'noindex,nofollow';
        }
        if ($request->getPathInfo() === '/Search') {
            return 'noindex,follow';
        }
        if ($request->getPathInfo() === '/Forum/Interesting') {
            return 'noindex,nofollow';
        }
        return 'index,follow';
    }

    function metaCanonical(): string
    {
        /** @var Request $request */
        $request = $this->app['request'];
        return 'https://' . $request->getHost() . $request->getPathInfo();
    }
}
