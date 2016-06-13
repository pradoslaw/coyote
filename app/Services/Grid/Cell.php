<?php

namespace Coyote\Services\Grid;

class Cell implements CellInterface
{
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
     * @param mixed $data
     */
    public function __construct(Column $column, $data)
    {
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
        return $this->value;
    }

    protected function setupValue()
    {
        $value = null;

        if (is_array($this->data) || $this->data instanceof \ArrayAccess) {
            $this->value = array_get($this->data, $this->column->getName());
        } elseif (is_object($this->data)) {
            $this->value = object_get($this->data, $this->column->getName());
        }
    }

    protected function decorate()
    {
        foreach ($this->column->getDecorators() as $decorator) {
            $decorator->decorate($this);
        }
    }
}
