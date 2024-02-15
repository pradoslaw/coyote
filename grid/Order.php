<?php

namespace Boduch\Grid;

class Order
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @param string|null $column
     * @param string|null $direction
     */
    public function __construct($column = null, $direction = null)
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }
}
