<?php

namespace Boduch\Grid;

class Rows implements \IteratorAggregate, \Countable
{
    /**
     * @var Row[]
     */
    protected $rows = [];

    /**
     * @param array $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->rows);
    }

    /**
     * Add row
     *
     * @param Row $row
     * @return Rows
     */
    public function addRow(Row $row)
    {
        $this->rows[] = $row;

        return $this;
    }

    /**
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->rows);
    }

    /**
     * Returns the iterator as an array
     *
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this->getIterator(), true);
    }
}
