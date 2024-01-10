<?php
namespace Coyote\Services;

use Illuminate\View\View;
use Spatie\SchemaOrg\BreadcrumbList;
use Spatie\SchemaOrg\Schema;

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
        $this->breadcrumbs[] = ['name' => $name, 'url' => $url];
    }

    public function render(): View
    {
        return view('components/breadcrumb', [
            'breadcrumbs' => $this->breadcrumbs,
            'schema'      => $this->schema(),
        ]);
    }

    private function schema(): ?BreadcrumbList
    {
        if (empty($this->breadcrumbs)) {
            return null;
        }
        return Schema::breadcrumbList()
            ->itemListElement(\array_map(
                fn(array $breadcrumb, int $index) => Schema::listItem()
                    ->position($index + 1)
                    ->identifier($breadcrumb['url'])
                    ->name($breadcrumb['name']),
                $this->breadcrumbs,
                array_keys($this->breadcrumbs),
            ));
    }
}
