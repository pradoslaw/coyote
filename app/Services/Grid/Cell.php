<?php

namespace Coyote\Services\Grid;

use Coyote\Services\Grid\Columns\Column;

class Cell
{
    /**
     * @var Column
     */
    protected $column;

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
        $this->value = $this->setupValue($data);
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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function setupValue($data)
    {
        $value = null;

        if (is_array($data) || $data instanceof \ArrayAccess) {
            $value = array_get($data, $this->column->getName());
        } elseif (is_object($data)) {
            $value = object_get($data, $this->column->getName());
        }

        return $value;
    }
}
