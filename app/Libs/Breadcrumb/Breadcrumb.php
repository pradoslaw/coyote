<?php

namespace Breadcrumb;

class Breadcrumb
{
    private $breadcrumbs = [];

    public function push($name, $url)
    {
        $this->breadcrumbs[] = ['name' => $name, 'url' => $url];
    }

    public function render()
    {
        return view('components/breadcrumb', ['breadcrumbs' => $this->breadcrumbs]);
    }
}