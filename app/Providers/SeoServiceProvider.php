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
        return 'https://' .
            $request->getHost() .
            $request->getPathInfo() .
            $this->queryString($request);
    }

    private function queryString(Request $request): string
    {
        return \rTrim('?' . \http_build_query($this->queryStringParams($request)), '?');
    }

    private function queryStringParams(Request $request): array
    {
        if (!$this->allowQueryParam($request)) {
            return [];
        }
        $queryParams = $this->queryParams($request);
        if (\array_key_exists('page', $queryParams)) {
            return ['page' => $queryParams['page']];
        }
        return [];
    }

    private function allowQueryParam(Request $request): bool
    {
        $uri = $request->getPathInfo();
        if ($uri === '/Forum') {
            return false;
        }
        return \str_starts_with($uri, '/Forum');
    }

    private function queryParams(Request $request): array
    {
        \parse_str($request->getQueryString(), $queryParams);
        return $queryParams;
    }
}
