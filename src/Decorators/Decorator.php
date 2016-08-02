<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

abstract class Decorator implements DecoratorInterface
{
    /**
     * @param Cell $cell
     * @return void
     */
    abstract public function decorate(Cell $cell);
}
