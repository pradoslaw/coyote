<?php

namespace Coyote\Services\Grid;

class Row implements \IteratorAggregate
{
    /**
     * @var CellInterface[]
     */
    protected $cells = [];

    /**
     * @param CellInterface $cell
     * @return $this
     */
    public function addCell(CellInterface $cell)
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
