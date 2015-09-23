<?php

namespace Breadcrumb;

class Breadcrumb implements \Countable
{
    private $breadcrumbs = [];

    public function count()
    {
        return count($this->breadcrumbs);
    }

    public function push($name, $url)
    {
        $this->breadcrumbs[] = ['name' => $name, 'url' => $url];
    }

    public function render()
    {
        return view('components/breadcrumb', ['breadcrumbs' => $this->breadcrumbs]);
    }
}
