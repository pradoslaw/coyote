<?php

namespace Boduch\Grid;

use Symfony\Component\HttpFoundation\ParameterBag;

class Cell implements CellInterface
{
    use AttributesTrait;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param Column $column
     * @param mixed $data       Raw row data (array or object)
     */
    public function __construct(Column $column, $data)
    {
        $this->attributes = new ParameterBag();
        $this->column = $column;
        $this->data = $data;

        $this->setupValue();
        $this->decorate();
    }

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->column;
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
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->column->isAutoescape() ? htmlspecialchars($this->value) : $this->value;
    }

    /**
     * @return mixed
     */
    public function getUnescapedValue()
    {
        return $this->value;
    }

    protected function setupValue()
    {
        if (is_array($this->data) || $this->data instanceof \ArrayAccess) {
            $this->value = array_get($this->data, $this->column->getName());
        } elseif (is_object($this->data)) {
            $this->value = object_get($this->data, $this->column->getName());
        }
    }

    protected function decorate()
    {
        foreach ($this->column->getDecorators() as $decorator) {
            // if decorator returns FALSE, we need to break the loop. next decorators WILL NOT be executed.
            if (false === $decorator->decorate($this)) {
                break;
            }
        }
    }
}
