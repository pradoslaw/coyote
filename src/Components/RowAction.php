<?php

namespace Boduch\Grid\Components;

abstract class RowAction extends Component
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * RowAction constructor.
     * @param \Closure $url
     */
    public function __construct(\Closure $url)
    {
        $this->setClosure($url);
    }

    /**
     * @return string
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * @param \Closure $closure
     */
    public function setClosure(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function buildActionUrl($data)
    {
        return $this->closure->call($this, $data);
    }
}
