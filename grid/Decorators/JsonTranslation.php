<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class JsonTranslation extends Decorator
{
    /**
     * @param Cell $cell
     */
    public function decorate(Cell $cell)
    {
        $cell->setValue(__($cell->getValue()));
    }
}
