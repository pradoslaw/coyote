<?php
namespace Coyote\Services;

use Coyote\Domain\Breadcrumb;
use Coyote\Domain\Html;
use Coyote\Domain\Seo;
use Coyote\Domain\Seo\Schema\BreadcrumbList;
use Coyote\Domain\StringHtml;

class Breadcrumbs
{
    private array $breadcrumbs = [];

    public function pushMany(array $breadcrumbs): void
    {
        foreach ($breadcrumbs as $name => $uri) {
            $this->push($name, $uri);
        }
    }

    public function push(string $name, string $url, bool $leafWithLink = false): void
    {
        $this->breadcrumbs[] = new Breadcrumb($name, $url, false, $leafWithLink);
    }

    public function render(): Html|string
    {
        if (empty($this->breadcrumbs)) {
            return '';
        }
        return new StringHtml(view('legacyComponents/breadcrumb', [
            'root_name'         => config('app.name'),
            'root_href'         => route('home'),
            'breadcrumbs'       => $this->breadcrumbsWithLeaf(),
            'schema_breadcrumb' => new Seo\Schema(new BreadcrumbList($this->breadcrumbs)),
        ]));
    }

    public function breadcrumbsModel(): array
    {
        return [
            'rootTitle'   => '4programmers',
            'rootHref'    => route('home'),
            'breadcrumbs' => array_map(fn(Breadcrumb $breadcrumb) => [
                'title' => $breadcrumb->name,
                'href'  => $breadcrumb->url,
            ], \array_slice($this->breadcrumbs, 0, -1)),
        ];
    }

    private function breadcrumbsWithLeaf(): array
    {
        return $this->lastBreadcrumbIsLeaf($this->breadcrumbs);
    }

    private function lastBreadcrumbIsLeaf(array $breadcrumbs): array
    {
        return [
            ...\array_slice($breadcrumbs, 0, -1),
            $this->last($breadcrumbs)->leaf(),
        ];
    }

    private function last(array $breadcrumbs): Breadcrumb
    {
        /** @var Breadcrumb $last */
        $last = \end($breadcrumbs);
        return $last;
    }
}
