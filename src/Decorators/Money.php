<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Money
{
    /**
     * @param Cell $cell
     */
    public function decorate(Cell $cell)
    {
        $cell->setValue(money_format('%.2n', (float) $cell->getValue()));
    }
}
