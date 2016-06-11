<?php

namespace Coyote\Services\Grid\Filters;

use Coyote\Services\Grid\Column;

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
