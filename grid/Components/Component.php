<?php

namespace Boduch\Grid\Components;

use Boduch\Grid\Grid;

abstract class Component
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @return mixed
     */
    abstract public function render();

    /**
     * @return Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @param Grid $grid
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return snake_case(class_basename($this));
    }

    /**
     * @param string $tag
     * @param string $content
     * @param array $attributes
     * @return \Illuminate\Support\HtmlString
     */
    protected function tag($tag, $content, array $attributes = [])
    {
        return $this->grid->getGridHelper()->getHtmlBuilder()->tag($tag, $content, $attributes);
    }
}
