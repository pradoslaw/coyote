<?php
namespace Coyote\Providers;

use Coyote\Domain\Seo\Schema;
use Coyote\View\Twig\TwigLiteral;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class SeoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Factory $view */
        $view = $this->app['view'];
        $view->composer('layout', function (View $view): void {
            $view->with(['schema_organization' => TwigLiteral::fromHtml(new Schema(new Schema\Organization()))]);
        });
    }
}
