<?php

namespace Boduch\Grid;

interface CellInterface
{
    /**
     * @return Column
     */
    public function getColumn();

    /**
     * @return mixed
     */
    public function getValue();
}
