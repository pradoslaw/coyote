<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

interface DecoratorInterface
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell);
}
