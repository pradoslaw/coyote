<?php

namespace Coyote\Services;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\View\View;
use Spatie\SchemaOrg\Schema;

/**
 * Prosta klasa sluzaca do budowania elementu obecnego na kazdej podstronie, czyli breadcrumb
 *
 * Class Breadcrumb
 * @package Coyote
 */
class Breadcrumb implements \Countable, Arrayable
{
    private $breadcrumbs = [];

    /**
     * Zwraca liczbe elementow w breadcrumb
     *
     * @return int
     */
    public function count()
    {
        return count($this->breadcrumbs);
    }

    /**
     * Umozliwia dodanie kolejnego elementu do "okruszkow". Element $name moze byc tablica okruszkow
     *
     * @param $name
     * @param null $url
     */
    public function push($name, $url = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->push($key, $value);
            }
        } else if (is_string($name)) { // we don't want to add empty value
            $this->breadcrumbs[] = ['name' => $name, 'url' => $url];
        }
    }

    public function render(): View
    {
        $schemaBreadcrumbs = \array_map(
            fn(array $breadcrumb, int $index) => Schema::listItem()
                ->position($index + 1)
                ->identifier($breadcrumb['url'])
                ->name($breadcrumb['name']),
            $this->breadcrumbs,
            array_keys($this->breadcrumbs),
        );
        return view('components/breadcrumb', [
            'breadcrumbs' => $this->breadcrumbs,
            'schema'      => empty($schemaBreadcrumbs)
                ? null
                : Schema::breadcrumbList()->itemListElement($schemaBreadcrumbs),
        ]);
    }

    public function toArray(): array
    {
        return $this->breadcrumbs;
    }
}
