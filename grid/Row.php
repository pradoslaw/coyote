<?php

namespace Boduch\Grid;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @property string $class
 * @property string $style
 */
class Row implements \IteratorAggregate
{
    use AttributesTrait;

    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var mixed|null
     */
    protected $raw;

    /**
     * @var CellInterface[]
     */
    protected $cells = [];

    /**
     * @param mixed|null $raw
     */
    public function __construct($raw = null)
    {
        $this->attributes = new ParameterBag();
        $this->raw = $raw;
    }

    /**
     * Grid can be accessed from helper functions.
     *
     * @param Grid $grid
     * @return $this
     */
    public function setGrid($grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @return Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @param CellInterface $cell
     * @return $this
     */
    public function addCell(CellInterface $cell)
    {
        $this->cells[$cell->getColumn()->getName()] = $cell;

        return $this;
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cells);
    }

    /**
     * Get cell object by name.
     * @param string $name  Column name
     * @return CellInterface|null
     */
    public function get($name)
    {
        return $this->cells[$name] ?? null;
    }

    /**
     * Get raw row data.
     *
     * @param string $name
     * @return mixed|null
     */
    public function raw($name)
    {
        return $this->raw[$name] ?? null;
    }

    /**
     * Get cell value.
     *
     * @param string $name  Column name
     * @return mixed
     */
    public function getValue($name)
    {
        return $this->cells[$name]->getValue();
    }
}
