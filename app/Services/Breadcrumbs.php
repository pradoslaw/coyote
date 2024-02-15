<?php
namespace Coyote\Services;

use Coyote\Domain\Breadcrumb;
use Coyote\Domain\Html;
use Coyote\Domain\Seo;
use Coyote\Domain\Seo\Schema\BreadcrumbList;
use Coyote\View\Twig\TwigLiteral;
use Illuminate\View\View;

class Breadcrumbs
{
    private array $breadcrumbs = [];

    public function pushMany(array $breadcrumbs): void
    {
        foreach ($breadcrumbs as $name => $uri) {
            $this->push($name, $uri);
        }
    }

    public function push(string $name, string $url): void
    {
        $this->breadcrumbs[] = new Breadcrumb($name, $url);
    }

    public function render(): View
    {
        return view('components/breadcrumb', [
            'root_name'         => config('app.name'),
            'root_href'         => route('home'),
            'breadcrumbs'       => $this->breadcrumbs,
            'schema_breadcrumb' => TwigLiteral::fromHtml($this->schema()),
        ]);
    }

    private function schema(): Html
    {
        if (empty($this->breadcrumbs)) {
            return new Html('');
        }
        return new Seo\Schema(new BreadcrumbList($this->breadcrumbs));
    }
}
