<?php

namespace Coyote\Services\Grid;

class Row implements \IteratorAggregate
{
    /**
     * @var Cell[]
     */
    protected $cells = [];

    /**
     * @param Cell $cell
     * @return $this
     */
    public function addCell(Cell $cell)
    {
        $this->cells[] = $cell;

        return $this;
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cells);
    }
}
