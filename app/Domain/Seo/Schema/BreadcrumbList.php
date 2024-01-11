<?php
namespace Coyote\Domain\Seo\Schema;

use Coyote\Domain\Breadcrumb;

class BreadcrumbList implements Thing
{
    /**
     * @param Breadcrumb[] $breadcrumbs
     */
    public function __construct(private array $breadcrumbs)
    {
    }

    public function schema(): array
    {
        return [
            '@context'        => 'http://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $this->listItems(),
        ];
    }

    private function listItems(): array
    {
        return \array_map([$this, 'listItem'], $this->breadcrumbs, \array_keys($this->breadcrumbs));
    }

    private function listItem(Breadcrumb $breadcrumb, int $index): array
    {
        return [
            '@type'    => 'ListItem',
            '@id'      => $breadcrumb->url,
            'name'     => $breadcrumb->name,
            'item'     => $breadcrumb->url,
            'position' => $index + 1,
        ];
    }
}
