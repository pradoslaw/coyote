<?php

namespace Coyote\Services\Grid\Decorators;

use Coyote\Services\Grid\Cell;

abstract class Decorator implements DecoratorInterface
{
    /**
     * @param Cell $cell
     * @return void
     */
    abstract public function decorate(Cell $cell);
}
