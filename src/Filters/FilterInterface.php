<?php

namespace Boduch\Grid\Filters;

use Boduch\Grid\Column;

interface FilterInterface
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
     * @return mixed
     */
    public function getOperator();

    /**
     * @return mixed
     */
    public function render();
}
