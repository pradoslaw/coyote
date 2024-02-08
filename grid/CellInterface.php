<?php

namespace Boduch\Grid;

use Symfony\Component\HttpFoundation\ParameterBag;

interface CellInterface
{
    /**
     * @return ParameterBag
     */
    public function attributes();

    /**
     * @return Column
     */
    public function getColumn();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     */
    public function setValue($value);
}
