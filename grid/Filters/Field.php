<?php

namespace Boduch\Grid\Filters;

use Boduch\Grid\Column;

interface Field
{
    /**
     * @return Column
     */
    public function getColumn();

    /**
     * @param Column $column
     */
    public function setColumn(Column $column);

    /**
     * @inheritdoc
     */
    public function setOperator($operator);

    /**
     * @return mixed
     */
    public function getOperator();

    /**
     * @return mixed
     */
    public function render();

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * Return field's name to build query (to filter data) or render filter element.
     *
     * @return string
     */
    public function getName();

    /**
     * Is filter input value empty?
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * @return array|string
     */
    public function getInput();
}
