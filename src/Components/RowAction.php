<?php

namespace Boduch\Grid\Components;

use Boduch\Grid\Grid;

abstract class RowAction
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var \Closure
     */
    protected $url;

    /**
     * RowAction constructor.
     * @param \Closure $url
     */
    public function __construct(\Closure $url)
    {
        $this->setUrl($url);
    }

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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param \Closure $url
     */
    public function setUrl(\Closure $url)
    {
        $this->url = $url;
    }

    /**
     * @param $data
     * @return mixed
     */
    abstract public function render($data);

    /**
     * @param $data
     * @return mixed
     */
    protected function getActionUrl($data)
    {
        return $this->url->call($this, $data);
    }

    /**
     * @param string $tag
     * @param string $content
     * @param array $attributes
     * @return \Illuminate\Support\HtmlString
     */
    protected function tag($tag, $content, array $attributes = [])
    {
        return $this->grid->getHtmlBuilder()->tag($tag, $content, $attributes);
    }
}
